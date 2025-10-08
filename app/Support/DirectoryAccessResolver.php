<?php

namespace App\Support;

use App\Models\DirectoryAccessRule;
use App\Models\TrustedSubnet;
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

    public function isIpTrusted(\Illuminate\Http\Request $req, array $dirSubnets): bool
    {
        $ip = $req->ip();
        $all = array_values(array_unique(array_filter(array_merge(
            $this->globalTrustedSubnets(),     // ← глобальные из trusted_subnets
            $dirSubnets ?? []                  // ← из directory_access_rules.trusted_subnets
        ))));
        foreach ($all as $cidr) {
            if (IpUtils::checkIp($ip, $cidr)) {  // ← ВОТ ТУТ проверка попадания IP в CIDR
                return true;
            }
        }
        return false;
    }


    /** Нормализация слэшей */
    protected function norm(string $p): string
    {
        $p = str_replace('\\', '/', $p);
        $p = preg_replace('#/+#', '/', $p);
        return trim($p, '/');
    }

    protected function globalTrustedSubnets(): array
    {
        static $cache;
        if ($cache !== null)
            return $cache;
        $cache = TrustedSubnet::query()
            ->where('is_enabled', true)
            ->pluck('cidr')
            ->all();
        return $cache;
    }

}
