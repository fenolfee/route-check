<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class FileProxyController extends Controller
{
    public function handle(Request $request, string $path)
    {
       
        $root = rtrim(env('FILEBROWSER_ROOT', '/mnt/pl'), '/'); // /mnt/pl
        $urlPath = ltrim($path, '/');           // например: "pl/red_kn/201202/1812_god.pdf" или "red_kn/201202/.."
        // если путь начинается с "pl/", срезаем (корень уже /mnt/pl)
        $rel = Str::startsWith($urlPath, 'pl/') ? substr($urlPath, 3) : $urlPath; // red_kn/201202/1812_god.pdf
        $fullPath = $this->normalize("$root/$rel");
        logger()->info('FileProxy map', compact('root', 'urlPath', 'rel', 'fullPath'));
        
        if (! File::exists($fullPath)) {
            abort(404, 'Файл не найден');
        }
        /*
        if (Str::endsWith(Str::lower($fullPath), '.pdf')) {
            abort(403, 'PDF запрещён');
        }
        */
        return Response::file($fullPath, ['Cache-Control' => 'public, max-age=3600']);
    }

    private function normalize(string $p): string
    {
        $p = str_replace('\\', '/', $p);
        return preg_replace('#/+#', '/', $p);
    }
}
