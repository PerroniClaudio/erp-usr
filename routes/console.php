<?php

use App\Jobs\ImportPresenzeJob;
use App\Jobs\ImportVehiclesData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vehicles:import', function () {
    ImportVehiclesData::dispatch()->onQueue('default');
})->purpose('Import vehicle data from Excel files');


Artisan::command('attendance:import {peid} {userId} {startDate} {endDate}', function ($peid, $userId, $startDate, $endDate) {
    ImportPresenzeJob::dispatch($peid, $userId, $startDate, $endDate)->onQueue('default');
})->purpose('Import attendance data from legacy system');
