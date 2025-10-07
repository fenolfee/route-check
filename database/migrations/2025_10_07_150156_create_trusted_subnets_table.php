<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trusted_subnets', function (Blueprint $table) {
            $table->id();
            $table->string('cidr')->unique();         // 192.168.0.0/16, 10.0.0.0/8, 2a02::/32 ...
            $table->string('label')->nullable();      // подпись/описание
            $table->boolean('is_enabled')->default(true);
            $table->unsignedBigInteger('user_id')->nullable(); // кто добавил/менял
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trusted_subnets');
    }
};
