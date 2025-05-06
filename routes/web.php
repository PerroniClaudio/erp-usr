<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->middleware(['auth'])->name('home');


require __DIR__ . '/auth.php';
require __DIR__ . '/standard.php';
require __DIR__ . '/test.php';
