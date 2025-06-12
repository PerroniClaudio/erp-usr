<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\FailedAttendanceController;
use App\Http\Controllers\TimeOffRequestController;
use App\Http\Controllers\OvertimeRequestController;

Route::group([
    'middleware' => ['auth', 'role:standard'],
    'prefix' => 'standard/attendances',
], function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/user', [AttendanceController::class, 'getUserAttendances'])->name('attendances.user-attendances');
    Route::get('/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:standard'],
    'prefix' => 'standard/business-trips',
], function () {
    Route::get('/', [BusinessTripController::class, 'index'])->name('business-trips.index');
    Route::get('/user', [BusinessTripController::class, 'getUserTrips'])->name('business-trips.user-trips');
    Route::get('/create', [BusinessTripController::class, 'create'])->name('business-trips.create');
    Route::post('/', [BusinessTripController::class, 'store'])->name('business-trips.store');
    Route::get('/{businessTrip}/edit', [BusinessTripController::class, 'edit'])->name('business-trips.edit');
    Route::put('/{businessTrip}', [BusinessTripController::class, 'update'])->name('business-trips.update');
    Route::delete('/{businessTrip}', [BusinessTripController::class, 'destroy'])->name('business-trips.destroy');
    Route::get('/validate-address', [BusinessTripController::class, 'validateAddress'])->name('business-trips.validate-address');
    Route::get('/{businessTrip}/pdf', [BusinessTripController::class, 'generatePdf'])->name('business-trips.pdf');
    Route::get('/pdf-batch', [BusinessTripController::class, 'generateMonthlyPdf'])->name('business-trips.pdf-batch');

    /** Spese */

    Route::get('/{businessTrip}/expenses', [BusinessTripController::class, 'expenses'])->name('business-trips.expenses');
    Route::get('/{businessTrip}/expenses/create', [BusinessTripController::class, 'createExpense'])->name('business-trips.expenses.create');
    Route::post('/{businessTrip}/expenses', [BusinessTripController::class, 'storeExpense'])->name('business-trips.expenses.store');
    Route::get('/{businessTrip}/expenses/{expense}/edit', [BusinessTripController::class, 'editExpense'])->name('business-trips.expenses.edit');
    Route::put('/{businessTripExpense}/expenses/upload-justification', [BusinessTripController::class, 'uploadExpenseJustification'])->name('business-trips.expenses.upload-justification');
    Route::patch('/{businessTrip}/expenses/{expense}', [BusinessTripController::class, 'updateExpense'])->name('business-trips.expenses.update');
    Route::delete('/{businessTrip}/expenses/{expense}', [BusinessTripController::class, 'destroyExpense'])->name('business-trips.expenses.destroy');
    Route::get('/{businessTripExpense}/download-justification', [BusinessTripController::class, 'downloadExpenseJustification'])->name('business-trips.expenses.download-justification');

    /** Spostamenti */
    Route::get('/{businessTrip}/transfers', [BusinessTripController::class, 'transfers'])->name('business-trips.transfers');
    Route::get('/{businessTrip}/transfers/create', [BusinessTripController::class, 'createTransfer'])->name('business-trips.transfers.create');
    Route::post('/{businessTrip}/transfers', [BusinessTripController::class, 'storeTransfer'])->name('business-trips.transfers.store');
    Route::get('/{businessTrip}/transfers/{transfer}/edit', [BusinessTripController::class, 'editTransfer'])->name('business-trips.transfers.edit');
    Route::put('/{businessTrip}/transfers/{transfer}', [BusinessTripController::class, 'updateTransfer'])->name('business-trips.transfers.update');
    Route::delete('/{businessTrip}/transfers/{transfer}', [BusinessTripController::class, 'destroyTransfer'])->name('business-trips.transfers.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:standard'],
    'prefix' => 'standard/time-off-requests',
], function () {
    Route::get('/', [TimeOffRequestController::class, 'index'])->name('time-off-requests.index');
    Route::get('/user', [TimeOffRequestController::class, 'getUserRequests'])->name('time-off-requests.user-requests');
    Route::get('/estimate-days', [TimeOffRequestController::class, 'estimateDays'])->name('time-off-requests.estimate-days');
    Route::get('/create', [TimeOffRequestController::class, 'create'])->name('time-off-requests.create');
    Route::post('/', [TimeOffRequestController::class, 'storeBatch'])->name('time-off-requests.store');
    Route::get('/{batch_id}/edit', [TimeOffRequestController::class, 'edit'])->name('time-off-requests.edit');
    Route::post('/{batch_id}', [TimeOffRequestController::class, 'updateBatch'])->name('time-off-requests.update');
    Route::delete('/{timeOffRequest}', [TimeOffRequestController::class, 'destroy'])->name('time-off-requests.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:standard'],
    'prefix' => 'standard',
], function () {
    Route::get('/{failed_attendance}/justify-attendance', [FailedAttendanceController::class, 'justify'])->name('failed-attendances.justify');
    Route::post('/{failed_attendance}/send-justification', [FailedAttendanceController::class, 'sendJustification'])->name('failed-attendances.send-justification');
});

Route::group([
    'middleware' => ['auth', 'role:standard'],
    'prefix' => 'standard/overtime-requests',
], function () {
    Route::get('/', [\App\Http\Controllers\OvertimeRequestController::class, 'index'])->name('overtime-requests.index');
    Route::get('/create', [\App\Http\Controllers\OvertimeRequestController::class, 'create'])->name('overtime-requests.create');
    Route::post('/', [\App\Http\Controllers\OvertimeRequestController::class, 'store'])->name('overtime-requests.store');
    Route::get('/list', [\App\Http\Controllers\OvertimeRequestController::class, 'listUserOvertimeRequests'])->name('overtime-requests.list');
    Route::get('/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'show'])->name('overtime-requests.show');
});
