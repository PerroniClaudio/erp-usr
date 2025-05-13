<?php

use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', function () {
    return view('home');
})->middleware(['auth'])->name('home');

Route::get('/vehicles/search', function () {
    $query = request('query');
    $vehicles = Vehicle::search($query)->get();
    $vehicles = collect($vehicles->sortByDesc('model'));
    return response()->json(["models" => $vehicles]);
})->middleware(['auth'])->name('vehicles.brands.search');



require __DIR__ . '/auth.php';
require __DIR__ . '/standard.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/test.php';
