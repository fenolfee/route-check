<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServerFilesController extends Controller
{
    /**
     * Базовая папка, которую показываем.
     * Переопредели через .env: FILES_BASE_PATH=/абсолютный/путь/к/папке
     */
    protected string $basePath;

    public function __construct()
    {
        // По умолчанию показываем storage/app; подставь свою папку через .env
        $this->basePath = rtrim(env('FILES_BASE_PATH', storage_path('app')), DIRECTORY_SEPARATOR);
    }

    public function index(Request $request)
    {
        // относительный путь внутри base, например: "", "docs", "docs/sub"
        $rel = trim($request->query('dir', ''), "/\\");
        [$absDir, $rel] = $this->resolveSafeDir($rel);

        if (!is_dir($absDir)) {
            abort(404, 'Каталог не найден');
        }

        // Сканируем директорию
        $entries = array_diff(scandir($absDir), ['.', '..']);

        $items = collect($entries)->map(function ($name) use ($absDir, $rel) {
            $abs = $absDir . DIRECTORY_SEPARATOR . $name;
            $isDir = is_dir($abs);
            $size  = $isDir ? null : (is_file($abs) ? filesize($abs) : null);
            $mtime = filemtime($abs) ?: null;

            return [
                'name'   => $name,
                'is_dir' => $isDir,
                'size'   => $size,
                'mtime'  => $mtime,
                'rel'    => ltrim($rel . '/' . $name, '/'),
            ];
        });

        // Сортировка: сначала папки по имени, потом файлы по имени
        $items = $items->sortBy(fn($x) => ($x['is_dir'] ? '0_' : '1_') . Str::lower($x['name']))->values();

        // Хлебные крошки
        $breadcrumbs = $this->breadcrumbs($rel);

        return view('files.index', [
            'basePath'    => $this->basePath,
            'currentRel'  => $rel,
            'items'       => $items,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function download(Request $request): BinaryFileResponse
    {
        $relFile = trim($request->query('file', ''), "/\\");
        if ($relFile === '') abort(404);

        [$abs, $rel] = $this->resolveSafePath($relFile);

        if (!is_file($abs)) {
            abort(404, 'Файл не найден');
        }

        // Скачивание
        return response()->download($abs, basename($abs));
    }

    /** Безопасно собрать абсолютный путь до папки */
    protected function resolveSafeDir(string $rel): array
    {
        $target = $this->normalizePath($this->basePath . DIRECTORY_SEPARATOR . $rel);
        $realBase = realpath($this->basePath);
        $realTarget = realpath($target);

        if ($realTarget === false) {
            // Папка может не иметь realpath (редко), но если её нет — 404
            abort(404, 'Каталог не найден');
        }

        if (!Str::startsWith($realTarget, $realBase)) {
            abort(403, 'Запрещено');
        }

        return [$realTarget, $rel];
    }

    /** Безопасно собрать абсолютный путь до файла/папки */
    protected function resolveSafePath(string $rel): array
    {
        $target = $this->normalizePath($this->basePath . DIRECTORY_SEPARATOR . $rel);
        $realBase = realpath($this->basePath);
        $realTarget = realpath($target);

        if ($realTarget === false) {
            abort(404, 'Не найдено');
        }
        if (!Str::startsWith($realTarget, $realBase)) {
            abort(403, 'Запрещено');
        }

        return [$realTarget, $rel];
    }

    /** Нормализация слэшей */
    protected function normalizePath(string $path): string
    {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    /** Крошки */
    protected function breadcrumbs(string $rel): array
    {
        $parts = $rel === '' ? [] : explode('/', $rel);
        $acc = [];
        $crumbs[] = ['label' => 'Корень', 'rel' => '']; // ссылка на /files без dir

        foreach ($parts as $p) {
            $acc[] = $p;
            $crumbs[] = [
                'label' => $p,
                'rel'   => implode('/', $acc),
            ];
        }
        return $crumbs;
    }
}
