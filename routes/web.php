<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerFilesController;
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
});

Route::get('{path}', [App\Http\Controllers\FileProxyController::class, 'handle'])
    ->where('path', '.*');

Route::get('/files', [ServerFilesController::class, 'index'])->name('files.index');
Route::get('/files/download', [ServerFilesController::class, 'download'])->name('files.download');
require __DIR__.'/auth.php';
