<?php

use App\Http\Controllers\DirectoryAccessController;
use App\Http\Controllers\FileProxyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerFilesController;
use App\Http\Controllers\Admin\TrustedSubnetController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
    });
    Route::get('/files', [ServerFilesController::class, 'index'])->name('files.index');
    Route::get('/files/download', [ServerFilesController::class, 'download'])->name('files.download');
});

require __DIR__ . '/auth.php';


Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/files/access', [DirectoryAccessController::class, 'edit'])->name('files.access.edit');
    Route::post('/files/access', [DirectoryAccessController::class, 'update'])->name('files.access.update');
});



Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/trusted-subnets', [TrustedSubnetController::class, 'index'])->name('admin.trusted-subnets.index');
    Route::post('/trusted-subnets', [TrustedSubnetController::class, 'store'])->name('admin.trusted-subnets.store');
    Route::put('/trusted-subnets/{trustedSubnet}', [TrustedSubnetController::class, 'update'])->name('admin.trusted-subnets.update');
    Route::delete('/trusted-subnets/{trustedSubnet}', [TrustedSubnetController::class, 'destroy'])->name('admin.trusted-subnets.destroy');
});



// прокси файлов тоже через проверку доступа
// если сюда зайдет кто-то кроме олега этот маршрут всегда должен внизу барахтаться, от греха подальше
Route::get('/iap/{path}', [FileProxyController::class, 'handle'])
    ->where('path', '.*')
    ->middleware('dir.access')
    ->name('files.proxy');
