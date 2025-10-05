<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportUsersFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * path - required path to the xlsx/xls file
     * --truncate - optional flag: truncate users table before import
     * --skip-header - whether to skip the first row (default: true)
     */
    protected $signature = 'import:users {path : Path to Excel file} {--truncate : Truncate users table before import} {--skip-header=true : Skip first row (header)}';

    protected $description = 'Import users from Excel. Columns: A=name, D=login, E=password';

    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Import users from Excel file.
     *
     * @param  string  $path  Path to the Excel file to import.
     *
     * @option bool $truncate Truncate users table before import.
     * @option bool $skipHeader Skip first row (header) if true.
     */
    /*******  aa445cbd-6831-4446-b7f4-194395325803  *******/
    public function handle()
    {
        $path = $this->argument('path');
        $doTruncate = $this->option('truncate');
        $skipHeader = filter_var($this->option('skip-header'), FILTER_VALIDATE_BOOLEAN);

        $this->info("Импорт из файла: {$path}");
        Log::info('ImportUsers started at '.Carbon::now()->toDateTimeString(), ['file' => $path]);

        if (! file_exists($path)) {
            $this->error("Файл не найден: {$path}");
            Log::warning('ImportUsers file not found', ['file' => $path]);

            return 1;
        }

        if ($doTruncate) {
            try {
                Schema::disableForeignKeyConstraints();

                if (DB::getDriverName() === 'sqlite') {
                    // В SQLite truncate как такового нет, делаем delete + сбрасываем AUTOINCREMENT
                    DB::table('users')->delete();
                    // Сброс последовательности (иначе id продолжит расти)
                    DB::statement("DELETE FROM sqlite_sequence WHERE name = 'users'");
                } else {
                    // Для MySQL/Postgres — штатный truncate
                    DB::table('users')->truncate();
                }

                Schema::enableForeignKeyConstraints();

                $this->info('Таблица users очищена.');
                Log::info('Users table truncated/cleared before import.', ['driver' => DB::getDriverName()]);
            } catch (\Throwable $e) {
                // На всякий случай вернём FK обратно
                try {
                    Schema::enableForeignKeyConstraints();
                } catch (\Throwable $ignored) {
                }
                $this->error('Не удалось очистить таблицу users: '.$e->getMessage());
                Log::error('Users truncate/clear failed', ['exception' => $e, 'driver' => DB::getDriverName()]);

                return 1;
            }
        }

        try {
            $reader = IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            $this->error('Ошибка при чтении Excel: '.$e->getMessage());
            Log::error('ImportUsers read error', ['exception' => $e]);

            return 1;
        }

        $rowCount = count($rows);
        $this->info("Найдено строк: {$rowCount}");
        Log::info('Rows found in spreadsheet', ['rows' => $rowCount]);

        $created = 0;
        $skipped = 0;
        $line = 0;

        foreach ($rows as $rIndex => $row) {
            $line = $rIndex;

            if ($skipHeader && $rIndex === 1) {
                $this->info('Пропускаю заголовок (строка 1).');

                continue;
            }

            $name = isset($row['A']) ? trim((string) $row['A']) : null;
            $login = isset($row['D']) ? trim((string) $row['D']) : null;
            $password = isset($row['E']) ? trim((string) $row['E']) : null;

            // basic validation
            if (empty($login) || empty($password)) {
                $this->warn("Строка {$rIndex}: пропущена — нет логина или пароля.");
                Log::warning('ImportUsers skipped row (missing login or password)', ['row' => $rIndex, 'name' => $name, 'login' => $login]);
                $skipped++;

                continue;
            }

            $attributes = [
                'name' => $name ?? $login,
                'password' => Hash::make($password),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            // set login vs email depending on DB
            if (Schema::hasColumn('users', 'login')) {
                $attributes['login'] = $login;
            } elseif (Schema::hasColumn('users', 'username')) {
                $attributes['username'] = $login;
            } elseif (Schema::hasColumn('users', 'email')) {
                // if login looks like email use it, otherwise store as email+placeholder domain
                if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                    $attributes['email'] = $login;
                } else {
                    // fallback: create a synthetic email to satisfy unique/email constraints
                    $safe = preg_replace('/[^a-z0-9._-]/i', '_', $login);
                    $attributes['email'] = $safe.'@import.local';
                }
            } else {
                // no known column to store login — try to store into meta JSON column if exists
                if (Schema::hasColumn('users', 'meta')) {
                    $attributes['meta'] = json_encode(['imported_login' => $login]);
                } else {
                    // final fallback: include in name
                    $attributes['name'] = ($name ?? '')." ({$login})";
                }
            }

            // Prevent duplicate by obvious unique keys if present (email/login/username)
            $exists = false;
            if (isset($attributes['email'])) {
                $exists = User::where('email', $attributes['email'])->exists();
            } elseif (isset($attributes['login'])) {
                $exists = User::where('login', $attributes['login'])->exists();
            } elseif (isset($attributes['username'])) {
                $exists = User::where('username', $attributes['username'])->exists();
            }

            if ($exists) {
                $this->warn("Строка {$rIndex}: пользователь с таким логином/почтой уже существует — пропускаю.");
                Log::warning('ImportUsers duplicate skipped', ['row' => $rIndex, 'login' => $login]);
                $skipped++;

                continue;
            }
            $attributes['role'] = 'user';
            try {
                // try using Eloquent create if possible
                $user = User::create($attributes);
                $created++;
                $this->info("Создан пользователь (строка {$rIndex}): id={$user->id}");
                Log::info('ImportUsers created user', ['row' => $rIndex, 'id' => $user->id, 'login' => $login]);
            } catch (\Throwable $e) {
                // fallback to DB insert
                try {
                    DB::table('users')->insert($attributes);
                    $created++;
                    $this->info("Вставлен пользователем через Query Builder (строка {$rIndex}).");
                    Log::info('ImportUsers inserted user via DB', ['row' => $rIndex, 'login' => $login]);
                } catch (\Throwable $e2) {
                    $this->error("Ошибка при создании пользователя на строке {$rIndex}: ".$e2->getMessage());
                    Log::error('ImportUsers failed to create user', ['row' => $rIndex, 'exception' => $e2]);
                    $skipped++;

                    continue;
                }
            }
        }

        $this->info("Импорт завершён. Создано: {$created}. Пропущено: {$skipped}.");
        Log::info('ImportUsers finished', ['created' => $created, 'skipped' => $skipped, 'file' => $path]);

        return 0;
    }
}
