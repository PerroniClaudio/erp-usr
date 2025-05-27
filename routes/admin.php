<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TimeOffRequestController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin',
], function () {
    Route::get('/home', function () {

        $usersStatus = AttendanceController::getAttendancesDataToday();
        $timeOffController = new TimeOffRequestController();
        $pendingTimeOffRequests = $timeOffController->getPendingTimeOffRequests();

        return view('admin.home', [
            'usersStatus' => $usersStatus,
            'pendingTimeOffRequests' => $pendingTimeOffRequests,
        ]);
    })->name('admin.home');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/personnel',
], function () {
    Route::resource('/groups', GroupController::class);
    Route::get('/groups/{group}/available-users', [GroupController::class, 'availableUsers'])->name('groups.available-users');
    Route::post('/groups/{group}/associate-users', [GroupController::class, 'associateUsers'])->name('groups.users.associate');
    Route::delete('/groups/{group}/dissociate-users/{user}', [GroupController::class, 'dissociateUsers'])->name('groups.users.dissociate');
    Route::get('/groups/users/available/{user}', [GroupController::class, 'availableForUser'])->name('groups.available-for-user');
    Route::post('/groups/users/{user}/associate-groups', [GroupController::class, 'associateGroups'])->name('groups.associate-groups');

    Route::resource('/companies', CompanyController::class);
    Route::get('/companies/users/available/{user}', [CompanyController::class, 'availableForUser'])->name('companies.available-for-user');
    Route::post('/companies/users/{user}/associate-companies', [CompanyController::class, 'associateCompanies'])->name('companies.associate-companies');
    Route::get('/companies/{company}/available-users', [CompanyController::class, 'availableUsers'])->name('companies.available-users');
    Route::post('/companies/{company}/associate-users', [CompanyController::class, 'associateUsers'])->name('companies.users.associate');
    Route::delete('/companies/{company}/dissociate-users/{user}', [CompanyController::class, 'dissociateUsers'])->name('companies.users.dissociate');

    Route::get('users/search-address', [UsersController::class, 'searchAddress'])->name('users.search-address');
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UsersController::class, 'edit'])->name('users.edit');
    Route::get('/users/{user}/export-cedolino', [UsersController::class, 'exportPdf'])->name('users.export-cedolino');
    Route::get('/users/{user}/export-presenze', [UsersController::class, 'exportPresenzePdf'])->name('users.export-presenze');
    Route::put('/users/{user}', [UsersController::class, 'updateData'])->name('users.update');
    Route::post('/users/{user}/store-residence', [UsersController::class, 'updateResidence'])->name('users.store-residence');
    Route::post('/users/{user}/store-location', [UsersController::class, 'updateLocation'])->name('users.store-location');
    Route::get('/users/{user}/add-vehicles', [UsersController::class, 'addVehicles'])->name('users.add-vehicles');
    Route::post('/users/{user}/store-vehicles', [UsersController::class, 'associateVehicle'])->name('users.store-vehicles');
    Route::get('/users/{user}/vehicles/{vehicle}/edit', [UsersController::class, 'editUserVehicle'])->name('users.vehicles.edit');
    Route::post('/users/{user}/vehicles/{vehicle}/update', [UsersController::class, 'updateUserVehicle'])->name('users.vehicles.update');
    Route::delete('/users/{user}/vehicles/{vehicle}/destroy', [UsersController::class, 'destroyUserVehicle'])->name('users.vehicles.destroy');
    Route::delete('/users/{user}/company/{company}/destroy', [UsersController::class, 'destroyUserCompany'])->name('users.company.destroy');
    Route::delete('/users/{user}/group/{group}/destroy', [UsersController::class, 'destroyUserGroup'])->name('users.group.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/time-off-requests',
], function () {
    Route::get('/', [TimeOffRequestController::class, 'adminIndex'])->name('admin.time-off.index');
    Route::get('/list', [TimeOffRequestController::class, 'listTimeOffRequests'])->name('admin.time-off.list');
    Route::get('/{time_off_request:batch_id}', [TimeOffRequestController::class, 'viewTimeOffRequest'])->name('admin.time-off.edit');
    Route::post('/{time_off_request}/approve', [TimeOffRequestController::class, 'approveTimeOffRequest'])->name('admin.time-off.approve');
    Route::post('/{time_off_request}/deny', [TimeOffRequestController::class, 'denyTimeOffRequest'])->name('admin.time-off.deny');
    Route::delete('/{time_off_request}', [TimeOffRequestController::class, 'deleteTimeOffRequest'])->name('admin.time-off.delete');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/attendance',
], function () {
    Route::get('/', [AttendanceController::class, 'adminIndex'])->name('admin.attendances.index');
    Route::get('/list', [AttendanceController::class, 'listAttendances'])->name('admin.attendances.list');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'viewAttendance'])->name('admin.attendances.edit');
});
