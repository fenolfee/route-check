<?php

namespace App\Http\Middleware;

use App\Support\DirectoryAccessResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class DirectoryAccessMiddleware
{
    public function __construct(protected DirectoryAccessResolver $resolver) {}

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

    protected function guessRelPath(Request $request): string
    {
        // 1) маршруты file browser: ?path=...
        if ($request->has('path')) {
            return trim((string)$request->query('path'), '/\\');
        }

        // 2) file-proxy/{path} (wildcard)
        if ($request->route() && $request->route()->parameter('path')) {
            // path может быть абсолютным — переводим в относительный от корня
            $param = (string)$request->route()->parameter('path');
            $param = str_replace('iap','/mnt',$param); // твоя замена, если надо оставить
            // если это абсолютный путь — конвертируем в относительный
            if (Str::startsWith($param, ['/','\\'])) {
                $rel = ltrim(Str::after($param, $this->resolver->root()), '/');
            } else {
                $rel = $param;
            }
            // если это файл — берём каталог файла
            if (str_contains($rel, '/')) {
                return trim(dirname($rel), '/\\');
            }
            return trim($rel, '/\\');
        }

        return '';
    }
}
