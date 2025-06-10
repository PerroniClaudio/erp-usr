<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OvertimeType;

class OvertimeTypeSeeder extends Seeder {
    public function run(): void {
        OvertimeType::insert([
            [
                'name' => 'Straordinario notturno',
                'acronym' => 'STN',
            ],
            [
                'name' => 'Straordinario festivo',
                'acronym' => 'STF',
            ],
            [
                'name' => 'Straordinario notturno e festivo',
                'acronym' => 'STNF',
            ],
        ]);
    }
}
