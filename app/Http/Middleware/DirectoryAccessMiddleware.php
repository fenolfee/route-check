<?php

namespace App\Http\Middleware;

use App\Support\DirectoryAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

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
    public function handle(Request $request, Closure $next): Response
    {
        // Определяем относительный путь
        $rel = $this->guessRelPath($request);

        $rule = $this->resolver->findRule($rel);
        $access = $rule['access'];

        if ($access === 'open') {
            return $next($request);
        }

        if ($access === 'trusted') {
            $trusted = $this->resolver->isIpTrusted($request, $rule['trusted_subnets'] ?? []);
            if ($trusted) {
                return $next($request);
            }
            // не из доверенных — требуем логин
            if (!auth()->check()) {
                return redirect()->guest(route('login'));
            }
            return $next($request);
        }

        // closed — только авторизованным
        if (!auth()->check()) {
            return redirect()->guest(route('login'));
        }
        return $next($request);
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

}
