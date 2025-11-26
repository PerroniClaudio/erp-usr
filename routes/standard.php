<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\DailyTravelController;
use App\Http\Controllers\FailedAttendanceController;
use App\Http\Controllers\TimeOffRequestController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UserScheduleController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR'],
    'prefix' => 'standard/attendances',
], function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/user', [AttendanceController::class, 'getUserAttendances'])->name('attendances.user-attendances');
    Route::get('/scheduled-slots', [AttendanceController::class, 'scheduledSlots'])->name('attendances.scheduled-slots');
    Route::get('/export-presenze', [UsersController::class, 'exportCurrentUserPresenze'])->name('attendances.export-presenze');
    Route::get('/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('/', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
});

Route::group([
    'middleware' => ['auth', 'role_or_permission:admin|business-trips.access'],
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
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR|admin'],
    'prefix' => 'standard/daily-travels',
], function () {
    Route::get('/', [DailyTravelController::class, 'index'])->name('daily-travels.index');
    Route::get('/pdf-batch', [DailyTravelController::class, 'pdfBatch'])->name('daily-travels.pdf-batch');
    Route::get('/create', [DailyTravelController::class, 'create'])->name('daily-travels.create');
    Route::post('/', [DailyTravelController::class, 'store'])->name('daily-travels.store');
    Route::get('/{dailyTravel}/pdf', [DailyTravelController::class, 'pdf'])->name('daily-travels.pdf');
    Route::get('/{dailyTravel}', [DailyTravelController::class, 'show'])->name('daily-travels.show');
    Route::delete('/{dailyTravel}', [DailyTravelController::class, 'destroy'])->name('daily-travels.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR'],
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
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR'],
    'prefix' => 'standard',
], function () {
    Route::get('/{failed_attendance}/justify-attendance', [FailedAttendanceController::class, 'justify'])->name('failed-attendances.justify');
    Route::post('/{failed_attendance}/send-justification', [FailedAttendanceController::class, 'sendJustification'])->name('failed-attendances.send-justification');
});

Route::group([
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR'],
    'prefix' => 'standard/overtime-requests',
], function () {
    Route::get('/', [\App\Http\Controllers\OvertimeRequestController::class, 'index'])->name('overtime-requests.index');
    Route::get('/create', [\App\Http\Controllers\OvertimeRequestController::class, 'create'])->name('overtime-requests.create');
    Route::post('/', [\App\Http\Controllers\OvertimeRequestController::class, 'store'])->name('overtime-requests.store');
    Route::get('/list', [\App\Http\Controllers\OvertimeRequestController::class, 'listUserOvertimeRequests'])->name('overtime-requests.list');
    Route::get('/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'show'])->name('overtime-requests.show');
});

Route::group([
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR'],
    'prefix' => 'standard/user-schedule-request',
], function () {
    Route::get('/', [UserScheduleController::class, 'requestForm'])->name('user-schedule-request.index');
    Route::post('/', [UserScheduleController::class, 'submitRequest'])->name('user-schedule-request.store');
});

Route::group([
    'middleware' => ['auth', 'role:standard|Responsabile HR|Operatore HR|admin'],
    'prefix' => 'standard/profile',
], function () {
    Route::get('/', [UsersController::class, 'editProfile'])->name('standard.profile.edit');
    Route::put('/personal-data', [UsersController::class, 'updateProfile'])->name('standard.profile.update');
    Route::post('/residence', [UsersController::class, 'updateProfileResidence'])->name('standard.profile.residence');
    Route::post('/location', [UsersController::class, 'updateProfileLocation'])->name('standard.profile.location');
    Route::get('/search-address', [UsersController::class, 'searchAddress'])->name('standard.profile.search-address');
});

// Routes for announcements (accessible to all authenticated users)
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'standard/announcements',
], function () {
    Route::get('/unread', [AnnouncementController::class, 'unread'])->name('announcements.unread');
    Route::post('/{announcement}/mark-as-read', [AnnouncementController::class, 'markAsRead'])->name('announcements.mark-as-read');
});
