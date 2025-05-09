<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\UsersController;
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

    Route::resource('/companies', CompanyController::class);
    Route::get('/companies/{company}/available-users', [CompanyController::class, 'availableUsers'])->name('companies.available-users');
    Route::post('/companies/{company}/associate-users', [CompanyController::class, 'associateUsers'])->name('companies.users.associate');
    Route::delete('/companies/{company}/dissociate-users', [CompanyController::class, 'dissociateUsers'])->name('companies.users.dissociate');

    Route::get('users/search-address', [UsersController::class, 'searchAddress'])->name('users.search-address');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UsersController::class, 'edit'])->name('users.edit');
    Route::get('/users/{user}/export-cedolino', [UsersController::class, 'exportPdf'])->name('users.export-cedolino');
    Route::put('/users/{user}', [UsersController::class, 'updateData'])->name('users.update');
    Route::post('/users/{user}/store-residence', [UsersController::class, 'updateResidence'])->name('users.store-residence');
    Route::post('/users/{user}/store-location', [UsersController::class, 'updateLocation'])->name('users.store-location');
});
