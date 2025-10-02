<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FailedAttendanceController;
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
        $timeOffController = new TimeOffRequestController;
        $pendingTimeOffRequests = $timeOffController->getPendingTimeOffRequests();
        $failedAttendancesController = new FailedAttendanceController;
        $failedAttendances = $failedAttendancesController->getPendingFailedAttendances();
        $overtimeController = new \App\Http\Controllers\OvertimeRequestController;
        $pendingOvertimeRequests = $overtimeController->getPendingOvertimeRequests();

        return view('admin.home', [
            'usersStatus' => $usersStatus,
            'pendingTimeOffRequests' => $pendingTimeOffRequests,
            'failedAttendancesRequests' => $failedAttendances,
            'pendingOvertimeRequests' => $pendingOvertimeRequests,
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
    Route::get('/users/{user}/export-nota-spese', [\App\Http\Controllers\NotaSpeseController::class, 'exportMonthly'])->name('users.export-nota-spese');
    Route::get('/users/{user}/export-anomalie', [UsersController::class, 'exportAnomaliesPdf'])->name('users.export-anomalie');
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
    Route::get('/create', [TimeOffRequestController::class, 'adminCreate'])->name('admin.time-off.create');
    Route::post('/create', [TimeOffRequestController::class, 'adminStoreBatch'])->name('admin.time-off.store');
    Route::get('/list', [TimeOffRequestController::class, 'listTimeOffRequests'])->name('admin.time-off.list');
    Route::get('/{time_off_request:batch_id}', [TimeOffRequestController::class, 'viewTimeOffRequest'])->name('admin.time-off.edit');
    Route::post('/{time_off_request}/approve', [TimeOffRequestController::class, 'approveTimeOffRequest'])->name('admin.time-off.approve');
    Route::post('/{time_off_request}/deny', [TimeOffRequestController::class, 'denyTimeOffRequest'])->name('admin.time-off.deny');
    Route::delete('/{time_off_request}', [TimeOffRequestController::class, 'deleteTimeOffRequest'])->name('admin.time-off.delete');
    Route::put('/{time_off_request:batch_id}/update-type', [TimeOffRequestController::class, 'updateTimeOffType'])->name('admin.time-off.update-type');
    Route::patch('/{time_off_request}/update-single-type', [TimeOffRequestController::class, 'updateSingleRequestType'])->name('admin.time-off.update-single-type');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/attendance',
], function () {
    Route::get('/', [AttendanceController::class, 'adminIndex'])->name('admin.attendances.index');
    Route::get('/list', [AttendanceController::class, 'listAttendances'])->name('admin.attendances.list');
    Route::get('/create', [AttendanceController::class, 'create'])->name('admin.attendances.create');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'viewAttendance'])->name('admin.attendances.edit');
    Route::get('/{failedAttendance}/handle', [FailedAttendanceController::class, 'handleFailedAttendance'])->name('admin.failed-attendances.edit');
    Route::post('/{failedAttendance}/approve', [FailedAttendanceController::class, 'approveFailedAttendance'])->name('admin.failed-attendances.approve');
    Route::post('/{failedAttendance}/deny', [FailedAttendanceController::class, 'denyFailedAttendance'])->name('admin.failed-attendances.deny');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/overtime-requests',
], function () {
    Route::get('/', [\App\Http\Controllers\OvertimeRequestController::class, 'adminIndex'])->name('admin.overtime-requests.index');
    Route::get('/create', [\App\Http\Controllers\OvertimeRequestController::class, 'adminCreate'])->name('admin.overtime-requests.create');
    Route::get('/list', [\App\Http\Controllers\OvertimeRequestController::class, 'listOvertimeRequests'])->name('admin.overtime-requests.list');
    Route::get('/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'adminShow'])->name('admin.overtime-requests.show');
    Route::post('/store', [\App\Http\Controllers\OvertimeRequestController::class, 'adminStore'])->name('admin.overtime-requests.store');
    Route::post('/{overtimeRequest}/approve', [\App\Http\Controllers\OvertimeRequestController::class, 'approve'])->name('admin.overtime-requests.approve');
    Route::post('/{overtimeRequest}/deny', [\App\Http\Controllers\OvertimeRequestController::class, 'deny'])->name('admin.overtime-requests.deny');
});
