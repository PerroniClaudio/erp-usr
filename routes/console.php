<?php

use App\Jobs\ImportVehiclesData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('vehicles:import', function () {
    ImportVehiclesData::dispatch();
})->purpose('Import vehicle data from Excel files');
