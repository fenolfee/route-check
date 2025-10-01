<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class FileProxyController extends Controller
{
    public function handle(Request $request, $path)
    {
       
        $fullPath = str_replace('iap', '/mnt', $path);
        logger("🔍 Запрос к файлу: {$fullPath}");
        $file = File::exists($fullPath);
      
        if (! File::exists($fullPath)) {
            abort(404, 'Файл не найден');
        }

        if (str_ends_with($fullPath, '.pdf')) {
            abort(403, 'PDF запрещён нахрен');
        }

        return Response::file($fullPath); // пока так
    }
}
