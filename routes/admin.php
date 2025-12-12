<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BusinessTripController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DailyTravelController;
use App\Http\Controllers\HeadquartersController;
use App\Http\Controllers\FailedAttendanceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\TimeOffRequestController;
use App\Http\Controllers\TimeOffAmountController;
use App\Http\Controllers\UsersController;
use App\Models\WeeklyScheduleCompletion;
use App\Models\UserScheduleChangeRequest;
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
        $weekStart = now()->startOfWeek(\Carbon\Carbon::MONDAY);
        $completion = WeeklyScheduleCompletion::whereDate('week_start', $weekStart)->first();
        $approvalPending = ! ($completion && $completion->completed_at);
        $pendingScheduleRequests = UserScheduleChangeRequest::with('user')
            ->where('status', UserScheduleChangeRequest::STATUS_PENDING)
            ->orderBy('week_start')
            ->limit(5)
            ->get();

        return view('admin.home', [
            'usersStatus' => $usersStatus,
            'pendingTimeOffRequests' => $pendingTimeOffRequests,
            'failedAttendancesRequests' => $failedAttendances,
            'pendingOvertimeRequests' => $pendingOvertimeRequests,
            'approvalPending' => $approvalPending,
            'weekStart' => $weekStart,
            'pendingScheduleRequests' => $pendingScheduleRequests,
        ]);
    })->name('admin.home');
});

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin/personnel',
], function () {
    Route::middleware('role:admin')->group(function () {
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

        Route::get('/user-schedules', [\App\Http\Controllers\UserScheduleController::class, 'index'])->name('user-schedules.index');
        Route::get('/user-schedules/{user}', [\App\Http\Controllers\UserScheduleController::class, 'show'])->name('user-schedules.show');
        Route::post('/user-schedules', [\App\Http\Controllers\UserScheduleController::class, 'store'])->name('user-schedules.store');
        Route::get('/user-schedule-requests', [\App\Http\Controllers\UserScheduleController::class, 'adminRequestsIndex'])->name('admin.user-schedule-requests.index');
        Route::get('/user-schedule-requests/{userScheduleChangeRequest}', [\App\Http\Controllers\UserScheduleController::class, 'adminRequestsShow'])->name('admin.user-schedule-requests.show');
        Route::post('/user-schedule-requests/{userScheduleChangeRequest}/approve', [\App\Http\Controllers\UserScheduleController::class, 'adminRequestsApprove'])->name('admin.user-schedule-requests.approve');
        Route::post('/user-schedule-requests/{userScheduleChangeRequest}/deny', [\App\Http\Controllers\UserScheduleController::class, 'adminRequestsDeny'])->name('admin.user-schedule-requests.deny');
        Route::get('/users/{user}/export-nota-spese', [\App\Http\Controllers\NotaSpeseController::class, 'exportMonthly'])->name('users.export-nota-spese');
        Route::get('/users/{user}/add-vehicles', [UsersController::class, 'addVehicles'])->name('users.add-vehicles');
        Route::post('/users/{user}/store-vehicles', [UsersController::class, 'associateVehicle'])->name('users.store-vehicles');
        Route::get('/users/{user}/vehicles/{vehicle}/edit', [UsersController::class, 'editUserVehicle'])->name('users.vehicles.edit');
        Route::post('/users/{user}/vehicles/{vehicle}/update', [UsersController::class, 'updateUserVehicle'])->name('users.vehicles.update');
        Route::delete('/users/{user}/vehicles/{vehicle}/destroy', [UsersController::class, 'destroyUserVehicle'])->name('users.vehicles.destroy');
        Route::delete('/users/{user}/company/{company}/destroy', [UsersController::class, 'destroyUserCompany'])->name('users.company.destroy');
        Route::delete('/users/{user}/group/{group}/destroy', [UsersController::class, 'destroyUserGroup'])->name('users.group.destroy');
        Route::post('/users/{user}/assign-role', [UsersController::class, 'assignRole'])->name('users.assign-role');
        Route::post('/users/{user}/remove-role', [UsersController::class, 'removeRole'])->name('users.remove-role');

        //Viaggi giornalieri 

        Route::get('/daily-trip-structure/{user}/{company}', [\App\Http\Controllers\DailyTravelStructureController::class, 'edit'])->name('admin.user.daily-trip-structure.edit');
        Route::post('/daily-trip-structure/{user}/{company}/edit-vehicle', [\App\Http\Controllers\DailyTravelStructureController::class, 'updateVehicle'])->name('admin.user.daily-trip-structure.edit-vehicle');
        Route::post('/daily-trip-structure/{user}/{company}/steps', [\App\Http\Controllers\DailyTravelStructureController::class, 'storeStep'])->name('admin.user.daily-trip-structure.steps.store');
        Route::post('/daily-trip-structure/{user}/{company}/steps/reorder', [\App\Http\Controllers\DailyTravelStructureController::class, 'reorderSteps'])->name('admin.user.daily-trip-structure.steps.reorder');
        Route::put('/daily-trip-structure/{user}/{company}/steps/{step}', [\App\Http\Controllers\DailyTravelStructureController::class, 'updateStep'])->name('admin.user.daily-trip-structure.steps.update');
        Route::delete('/daily-trip-structure/{user}/{company}/steps/{step}', [\App\Http\Controllers\DailyTravelStructureController::class, 'destroyStep'])->name('admin.user.daily-trip-structure.steps.destroy');
    });

     Route::middleware('role:admin|Responsabile HR')->group(function () {
            Route::get('/users/search-address', [UsersController::class, 'searchAddress'])->name('users.search-address');
    });

    Route::middleware('role:admin|Responsabile HR|Operatore HR')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/user-roles', [UsersController::class, 'manageRoles'])->name('users.roles');
        Route::get('/users/{user}', [UsersController::class, 'edit'])->name('users.edit');
        Route::get('/users/{user}/export-cedolino', [UsersController::class, 'exportPdf'])->name('users.export-cedolino');
        Route::get('/users/{user}/export-presenze', [UsersController::class, 'exportPresenzePdf'])->name('users.export-presenze');
        Route::get('/users/{user}/export-anomalie', [UsersController::class, 'exportAnomaliesPdf'])->name('users.export-anomalie');
        Route::get('/users/{user}/export-cedolino-anomalie', [UsersController::class, 'exportAnomaliesCedolinoPdf'])->name('users.export-cedolino-anomalie');
        Route::get('/users/{user}/default-schedules/calendar', [UsersController::class, 'showDefaultSchedule'])->name('users.default-schedules.calendar');
        Route::post('/users/{user}/default-schedules', [UsersController::class, 'updateDefaultSchedules'])->name('users.default-schedules.update');
        Route::post('/users/{user}/default-schedules/generate', [UsersController::class, 'generateDefaultSchedules'])->name('users.default-schedules.generate');
        Route::post('/time-off-amounts/calculate', [TimeOffAmountController::class, 'calculateResidual'])->name('time-off-amounts.calculate');
        Route::post('/time-off-amounts/monthly', [TimeOffAmountController::class, 'getMonthlyAmounts'])->name('time-off-amounts.monthly');
        Route::post('/time-off-amounts/usage', [TimeOffAmountController::class, 'getMonthlyUsage'])->name('time-off-amounts.usage');
        Route::post('/time-off-amounts', [TimeOffAmountController::class, 'store'])->name('time-off-amounts.store');
    });

    Route::middleware('role:admin|Responsabile HR')->group(function () {
        Route::put('/users/{user}', [UsersController::class, 'updateData'])->name('users.update');
        Route::put('/users/{user}/daily-travel-preferences', [UsersController::class, 'updateDailyTravelPreferences'])->name('users.daily-travel-preferences.update');
        Route::post('/users/{user}/store-residence', [UsersController::class, 'updateResidence'])->name('users.store-residence');
        Route::post('/users/{user}/store-location', [UsersController::class, 'updateLocation'])->name('users.store-location');
    });
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/headquarters',
], function () {
    Route::get('/search-address', [HeadquartersController::class, 'searchAddress'])->name('headquarters.search-address');
    Route::get('/', [HeadquartersController::class, 'index'])->name('headquarters.index');
    Route::get('/create', [HeadquartersController::class, 'create'])->name('headquarters.create');
    Route::post('/', [HeadquartersController::class, 'store'])->name('headquarters.store');
    Route::get('/{headquarter}/edit', [HeadquartersController::class, 'edit'])->name('headquarters.edit');
    Route::put('/{headquarter}', [HeadquartersController::class, 'update'])->name('headquarters.update');
    Route::delete('/{headquarter}', [HeadquartersController::class, 'destroy'])->name('headquarters.destroy');
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
    'prefix' => 'admin/business-trips',
], function () {
    Route::get('/', [BusinessTripController::class, 'adminIndex'])->name('admin.business-trips.index');
    Route::get('/export', [BusinessTripController::class, 'adminGenerateMonthlyPdf'])->name('admin.business-trips.export');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/daily-travels',
], function () {
    Route::get('/', [DailyTravelController::class, 'adminIndex'])->name('admin.daily-travels.index');
    Route::get('/create', [DailyTravelController::class, 'adminCreate'])->name('admin.daily-travels.create');
    Route::post('/', [DailyTravelController::class, 'adminStore'])->name('admin.daily-travels.store');
    Route::get('/export', [DailyTravelController::class, 'adminPdfBatch'])->name('admin.daily-travels.export');
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
    Route::delete('/{overtimeRequest}', [\App\Http\Controllers\OvertimeRequestController::class, 'destroy'])->name('admin.overtime-requests.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/announcements',
], function () {
    Route::get('/', [\App\Http\Controllers\AnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::get('/create', [\App\Http\Controllers\AnnouncementController::class, 'create'])->name('admin.announcements.create');
    Route::post('/', [\App\Http\Controllers\AnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::get('/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'show'])->name('admin.announcements.show');
    Route::get('/{announcement}/edit', [\App\Http\Controllers\AnnouncementController::class, 'edit'])->name('admin.announcements.edit');
    Route::put('/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'update'])->name('admin.announcements.update');
    Route::delete('/{announcement}', [\App\Http\Controllers\AnnouncementController::class, 'destroy'])->name('admin.announcements.destroy');
});

Route::group([
    'middleware' => ['auth', 'role:admin'],
    'prefix' => 'admin/files',
], function(){

    // Settori e protocolli (solo admin)
    Route::resource('/sectors', \App\Http\Controllers\FileObjectSectorController::class)->names('admin.sectors');
    Route::resource('/protocols', \App\Http\Controllers\ProtocolController::class)->names('admin.protocols');

});

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin/files',
], function(){
    Route::get('/index', [\App\Http\Controllers\FileObjectController::class, 'index'])->name('admin.files.index');
    Route::get('/search', [\App\Http\Controllers\FileObjectController::class, 'search'])->name('admin.files.search');
    Route::get('/folder/{hash}', [\App\Http\Controllers\FileObjectController::class, 'viewFolder'])->name('admin.files.folder');
    Route::post('/upload', [\App\Http\Controllers\FileObjectController::class, 'uploadFile'])->name('admin.files.upload');
    Route::post('/create-folder', [\App\Http\Controllers\FileObjectController::class, 'createFolder'])->name('admin.files.create-folder');
});
