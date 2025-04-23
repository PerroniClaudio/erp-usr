<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BusinessTripController;

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

    /** Spese */
    Route::get('/validate-address', [BusinessTripController::class, 'validateAddress'])->name('business-trips.validate-address');
    Route::get('/{businessTrip}/expenses', [BusinessTripController::class, 'expenses'])->name('business-trips.expenses');
    Route::get('/{businessTrip}/expenses/create', [BusinessTripController::class, 'createExpense'])->name('business-trips.expenses.create');
    Route::post('/{businessTrip}/expenses', [BusinessTripController::class, 'storeExpense'])->name('business-trips.expenses.store');
    Route::get('/{businessTrip}/expenses/{expense}/edit', [BusinessTripController::class, 'editExpense'])->name('business-trips.expenses.edit');
    Route::put('/{businessTrip}/expenses/{expense}', [BusinessTripController::class, 'updateExpense'])->name('business-trips.expenses.update');
    Route::delete('/{businessTrip}/expenses/{expense}', [BusinessTripController::class, 'destroyExpense'])->name('business-trips.expenses.destroy');
});
