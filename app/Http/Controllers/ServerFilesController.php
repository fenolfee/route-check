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
        $rel = trim($request->query('dir', ''), "/\\");
        [$absDir, $rel] = $this->resolveSafeDir($rel);

        if (!is_dir($absDir)) {
            abort(404, 'Каталог не найден');
        }
        $historicYears = (int) config('filebrowser.historic.years', 50);
        $historicEnabled = (bool) config('filebrowser.historic.enabled', true);

        $entries = array_diff(scandir($absDir), ['.', '..']);
        $resolver = app(\App\Support\DirectoryAccessResolver::class);

        // сразу строим items + наклеиваем правила через map
        $items = collect($entries)->map(function ($name) use ($absDir, $rel, $resolver) {
            $abs = $absDir . DIRECTORY_SEPARATOR . $name;
            $isDir = is_dir($abs);
            $item = [
                'name' => $name,
                'is_dir' => $isDir,
                'size' => $isDir ? null : (is_file($abs) ? filesize($abs) : null),
                'mtime' => @filemtime($abs) ?: null,
                'rel' => ltrim($rel . '/' . $name, '/'),
            ];
            // Для файла — правило папки; для папки — правило самой папки
            $targetRel = $isDir ? $item['rel'] : trim(dirname($item['rel']), '/\\');
            $rule = $resolver->findRule($targetRel);
            // Добавляем метаданные доступа
            $item['access'] = $rule['access'];                 // open|trusted|closed
            $item['rule_path'] = $rule['matched_path'] ?? '';     // у какого предка нашли
            $item['inherited'] = !empty($rule['inherited']);
            $item['trusted_dir_count'] = count($rule['trusted_subnets'] ?? []);
            $item['trusted_global_count'] = count($rule['global_subnets'] ?? []);

            return $item;
        });
        //проверяем на то что старше 70
        $items = $items->map(function ($it) use ($historicEnabled, $historicYears) {
            if ($historicEnabled && !empty($it['is_dir'])) {
                $last = basename($it['rel']);
                if (preg_match('/^\d{4}(\d{2})?$/', $last)) {
                    $year = (int) substr($last, 0, 4);
                    $month = strlen($last) >= 6 ? max(1, min(12, (int) substr($last, 4, 2))) : 1;
                    try {
                        $start = \Carbon\Carbon::create($year, $month, 1, 0, 0, 0, 'UTC');
                        $it['historic_open'] = \Carbon\Carbon::now('UTC')->greaterThanOrEqualTo($start->copy()->addYears($historicYears));
                    } catch (\Throwable $e) {
                        $it['historic_open'] = false;
                    }
                } else {
                    $it['historic_open'] = false;
                }
            } else {
                $it['historic_open'] = false;
            }
            return $it;
        });

        // Сортировка: папки, потом файлы; внутри — по имени (без учёта регистра)
        $items = $items
            ->sortBy(fn($x) => ($x['is_dir'] ? '0_' : '1_') . Str::lower($x['name']))
            ->values();

        $breadcrumbs = $this->breadcrumbs($rel);

        return view('files.index', [
            'basePath' => $this->basePath,
            'currentRel' => $rel,
            'items' => $items,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    public function download(Request $request): BinaryFileResponse
    {
        $relFile = trim($request->query('file', ''), "/\\");
        if ($relFile === '')
            abort(404);

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
                'rel' => implode('/', $acc),
            ];
        }
        return $crumbs;
    }
}
