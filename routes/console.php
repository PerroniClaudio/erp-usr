<?php

use App\Jobs\ImportPresenzeJob;
use App\Jobs\ImportTrasferte;
use App\Jobs\ImportVehiclesData;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vehicles:import', function () {
    ImportVehiclesData::dispatch()->onQueue('default');
})->purpose('Import vehicle data from Excel files');


Artisan::command('attendance:import {peid} {userId} {startDate} {endDate}', function ($peid, $userId, $startDate, $endDate) {
    ImportPresenzeJob::dispatch($peid, $userId, $startDate, $endDate)->onQueue('default');
})->purpose('Import attendance data from legacy system');

Artisan::command('businesstrip:import {peid} {userId} {startDate} {endDate}', function ($peid, $userId, $startDate, $endDate) {
    ImportTrasferte::dispatch($peid, $userId, $startDate, $endDate)->onQueue('default');
})->purpose('Import business trip data from legacy system');

Artisan::command('permission:give {userId} {permission}', function ($userId, $permission) {
    $user = \App\Models\User::findOrFail($userId);
    $user->assignRole($permission);
    $this->info("Permission '{$permission}' given to user with ID {$userId}.");
})->purpose('Give a permission to a user');


Schedule::call(function () {
    Mail::to(env('ADMIN_MAIL'))->send(new \App\Mail\FailedAttendance());
    Mail::to('c.perroni@ifortech.com')->send(new \App\Mail\FailedAttendance());
})->daily()->at('12:00')->name('daily_failed_attendance_email')->weekdays();
