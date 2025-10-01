<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
//для логинов
Route::get('/login', function () {
    return 'логин потом добавим';
});

Route::get('{path}', [App\Http\Controllers\FileProxyController::class, 'handle'])
    ->where('path', '.*');