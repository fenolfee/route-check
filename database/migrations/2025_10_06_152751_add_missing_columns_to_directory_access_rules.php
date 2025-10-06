<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('directory_access_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('directory_access_rules', 'path')) {
                $table->string('path')->default('');   // относительный путь
            }
            if (!Schema::hasColumn('directory_access_rules', 'access')) {
                $table->string('access', 16)->default('closed'); // open|trusted|closed
            }
            if (!Schema::hasColumn('directory_access_rules', 'trusted_subnets')) {
                $table->json('trusted_subnets')->nullable();
            }
            if (!Schema::hasColumn('directory_access_rules', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
            if (!Schema::hasColumn('directory_access_rules', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('directory_access_rules', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            // уникальность пути
            if (! $this->indexExists('directory_access_rules', 'directory_access_rules_path_unique')) {
                $table->unique('path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('directory_access_rules', function (Blueprint $table) {
            // откатывать можно выборочно; обычно не нужно
            // $table->dropUnique(['path']);
            // $table->dropColumn(['path','access','trusted_subnets','user_id','created_at','updated_at']);
        });
    }

    // Хелпер для проверки индекса (SQLite дружелюбнее с такой проверкой)
    private function indexExists(string $table, string $index): bool
    {
        try {
            return collect(DB::select("PRAGMA index_list('$table')"))
                ->contains(fn ($i) => strcasecmp($i->name ?? '', $index) === 0);
        } catch (\Throwable $e) {
            return false;
        }
    }
};
