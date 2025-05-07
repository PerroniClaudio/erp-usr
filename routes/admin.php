<?php

use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin',
], function () {
    Route::get('/home', function () {
        return view('admin.home');
    })->name('admin.home');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/personnel',
], function () {
    Route::resource('/groups', GroupController::class);
    Route::get('/groups/{group}/available-users', [GroupController::class, 'availableUsers'])->name('groups.available-users');
    Route::post('/groups/{group}/associate-users', [GroupController::class, 'associateUsers'])->name('groups.users.associate');
    Route::delete('/groups/{group}/dissociate-users', [GroupController::class, 'dissociateUsers'])->name('groups.users.dissociate');
});
