<?php

namespace App\Http\Controllers;

use App\Support\DirectoryAccessResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class FileProxyController extends Controller
{
    public function handle($path)
    {
        // твоя замена iap->/mnt
        $fullPath = str_replace('iap', '/mnt', $path);

        // (опционально) нормализация и защита от выхода из корня
        $resolver = app(DirectoryAccessResolver::class);
        $root = $resolver->root();
        $fullPath = str_replace('\\','/',$fullPath);

        if (!Str::startsWith($fullPath, $root)) {
            // Не даём выходить из корня
            abort(403, 'Недопустимый путь');
        }

        if (! File::exists($fullPath)) {
            abort(404, 'Файл не найден');
        }

        if (str_ends_with(Str::lower($fullPath), '.pdf')) {
            abort(403, 'PDF запрещён нахрен');
        }

        return Response::file($fullPath);
    }
}
