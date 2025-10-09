<?php

namespace App\Http\Middleware;

use App\Support\DirectoryAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;


class DirectoryAccessMiddleware
{
    public function __construct(protected DirectoryAccessResolver $resolver)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $rel = $this->guessRelPath($request);
        $rule = $this->resolver->findRule($rel);
        $access = $rule['access'] ?? 'closed';
        $trusted = $this->resolver->isIpTrusted($request, $rule['trusted_subnets'] ?? []);
        if ($this->shouldBypassForHistory($rel, $access)) {
            logger()->debug('dir.access.historic_bypass', compact('rel', 'access'));
            return $next($request);
        }
        switch ($access) {
            case 'open':
                return $next($request);

            case 'trusted':
                if ($trusted)
                    return $next($request);
                if (!auth()->check())
                    return redirect()->guest(route('login'));
                return $next($request);

            case 'closed':
            default:
                abort(403, 'Директория закрыта.');
        }

    }

    protected function guessRelPath(\Illuminate\Http\Request $request): string
    {
        // 1) из UI (браузер каталога)
        if ($request->has('path')) {
            return $this->norm((string) $request->query('path')); // уже относительный
        }

        // 2) из file-proxy/{path} (наш случай)
        $param = (string) $request->route('path');           // что пришло после /iap/
        $rel = ltrim(str_replace('\\', '/', $param), '/');     // "pl/red_kn/201202/1812_god.pdf" или "red_kn/..."

        // срежем ведущий "pl/" если есть (корень ФС уже /mnt/pl)
        if (Str::startsWith($rel, 'pl/')) {
            $rel = substr($rel, 3);
        }

        // для проверки директорий берём папку файла (dirname)
        $relDir = trim(dirname($rel), '/\\');                // "red_kn/201202"
        return $this->norm($relDir);
    }

    private function norm(string $p): string
    {
        $p = str_replace('\\', '/', $p);
        $p = preg_replace('#/+#', '/', $p);
        return trim($p, '/');
    }
    protected function shouldBypassForHistory(string $rel, string $access): bool
    {
        $cfg = config('filebrowser.historic', []);
        if (!($cfg['enabled'] ?? false))
            return false;

        // Ограничение по режимам
        $applyTo = $cfg['apply_to'] ?? ['trusted'];
        if (!in_array($access, $applyTo, true))
            return false;

        // Ограничение по префиксам (если задано)
        $onlyUnder = $cfg['only_under'] ?? [];
        if (!empty($onlyUnder)) {
            $hit = false;
            foreach ($onlyUnder as $prefix) {
                if ($prefix !== '' && Str::startsWith($rel, trim($prefix, '/') . '/')) {
                    $hit = true;
                    break;
                }
                if ($prefix !== '' && $rel === trim($prefix, '/')) {
                    $hit = true;
                    break;
                }
            }
            if (!$hit)
                return false;
        }

        return $this->isHistoricRel($rel, (int) ($cfg['years'] ?? 50));
    }

    protected function isHistoricRel(string $rel, int $years): bool
    {
        $rel = $this->norm($rel);
        if ($rel === '')
            return false;

        $last = basename($rel); // последний сегмент
        if (!preg_match('/^\d{4}(\d{2})?$/', $last)) {
            return false;
        }

        $year = (int) substr($last, 0, 4);
        $month = strlen($last) >= 6 ? max(1, min(12, (int) substr($last, 4, 2))) : 1;

        try {
            $start = Carbon::create($year, $month, 1, 0, 0, 0, 'UTC'); // начало периода
        } catch (\Throwable $e) {
            return false;
        }

        $threshold = $start->copy()->addYears($years);
        return Carbon::now('UTC')->greaterThanOrEqualTo($threshold);
    }


}
