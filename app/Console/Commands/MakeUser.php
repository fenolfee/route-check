<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class MakeUser extends Command
{
    /**
     * Название и сигнатура команды
     *
     * php artisan make:user {login} {password} {--name=}
     */
    protected $signature = 'make:user {login} {password} {--name=}';

    protected $description = 'Создаёт одного пользователя (login + password [+ name])';

    public function handle()
    {
        $login = $this->argument('login');
        $password = $this->argument('password');
        $name = $this->option('name') ?? $login;

        $attrs = [
            'name'     => $name,
            'password' => Hash::make($password),
        ];

        // Проверяем, какие колонки есть в таблице users
        if (Schema::hasColumn('users', 'login')) {
            $attrs['login'] = $login;
        } elseif (Schema::hasColumn('users', 'username')) {
            $attrs['username'] = $login;
        } elseif (Schema::hasColumn('users', 'email')) {
            $attrs['email'] = filter_var($login, FILTER_VALIDATE_EMAIL)
                ? $login
                : $login.'@local.dev';
        }

        $attrs['role'] = 'admin';
        // Проверка на дубликат
        $query = User::query();
        if (isset($attrs['login'])) {
            $query->where('login', $attrs['login']);
        } elseif (isset($attrs['username'])) {
            $query->where('username', $attrs['username']);
        } elseif (isset($attrs['email'])) {
            $query->where('email', $attrs['email']);
        }
        if ($query->exists()) {
            $this->error("Пользователь с таким логином уже существует.");
            return 1;
        }

        $user = User::create($attrs);
        $this->info("Создан пользователь: id={$user->id}, login={$login}");
        return 0;
    }
}
