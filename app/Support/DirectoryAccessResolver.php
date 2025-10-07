<?php

namespace App\Support;

use App\Models\DirectoryAccessRule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\IpUtils;

class DirectoryAccessResolver
{
    public function __construct(
        protected ?string $root = null
    ) {
        $this->root = $this->root ?? env('FILEBROWSER_ROOT', storage_path('app'));
        $this->root = $this->norm($this->root);
    }

    public function root(): string
    {
        return $this->root;
    }

    /** Вернёт относительный путь (от корня) для абсолютного */
    public function toRel(string $abs): string
    {
        $abs = $this->norm($abs);
        $root = $this->norm($this->root);
        return ltrim(Str::after($abs, $root), '/');
    }

    /** Найти самую "близкую" (глубокую) подходящую праву для пути */
    public function findRule(string $rel): array
    {
        $rel = trim($this->norm($rel), '/');
        $candidates = [''];
        if ($rel !== '') {
            $parts = explode('/', $rel);
            $acc = '';
            foreach ($parts as $p) {
                $acc = ltrim($acc . '/' . $p, '/');
                $candidates[] = $acc;
            }
            $candidates = array_reverse($candidates); // сначала самый глубокий
        }

        $rule = DirectoryAccessRule::query()
            ->whereIn('path', $candidates)
            ->get()
            ->sortByDesc(fn($r) => strlen($r->path))
            ->first();
            
        // по-умолчанию: closed
        return [
            'path' => $rule->path ?? '',
            'access' => $rule->access ?? 'closed',
            'trusted_subnets' => $rule?->trusted_subnets ?? [],
        ];
    }

    public function isIpTrusted(Request $req, array $subnets): bool
    {
        if (empty($subnets))
            return false;
        // IpUtils принимает массив CIDR/масок
        return IpUtils::checkIp($req->ip(), $subnets);
    }

    /** Нормализация слэшей */
    protected function norm(string $p): string
    {
        $p = str_replace('\\', '/', $p);
        $p = preg_replace('#/+#', '/', $p);
        return trim($p, '/');
    }

}
