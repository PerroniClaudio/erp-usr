<?php

namespace Database\Seeders;

use App\Models\AttendanceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        /*
            I tipi di default sono 
            Lavoro in sede	LS
            Lavoro c/o cliente	LC
            Corso	CO
            Straordinario	ST
            Recupero	RE
            Corso intra-lavorativo	CIL
            Corso extra-lavorativo	CEL
            Straordinario notturno	STN
            Straordinario festivo	STF
            Straordinario notturno/festivo	STNF
            Smartworking	SW
        */

        $attendanceTypes = [
            [
                'name' => 'Lavoro in sede',
                'acronym' => 'LS',

            ],
            [
                'name' => 'Lavoro c/o cliente',
                'acronym' => 'LC',
            ],
            [
                'name' => 'Corso',
                'acronym' => 'CO',
            ],
            [
                'name' => 'Straordinario',
                'acronym' => 'ST',
            ],
            [
                'name' => 'Recupero',
                'acronym' => 'RE',
            ],
            [
                'name' => 'Corso intra-lavorativo',
                'acronym' => 'CIL',
            ],
            [
                'name' => 'Corso extra-lavorativo',
                'acronym' => 'CEL',
            ],
            [
                'name' => 'Straordinario notturno',
                'acronym' => 'STN',
            ],
            [
                'name' => 'Straordinario festivo',
                'acronym' => 'STF',
            ],
            [
                'name' => 'Straordinario notturno/festivo',
                'acronym' => 'STNF',
            ],
            [
                'name' => 'Smartworking',
                'acronym' => 'SW',
            ],
            [
                'name' => 'Malattia',
                'acronym' => 'MA',
            ],
            [
                'name' => 'Congedo Parentale',
                'acronym' => 'CP',
            ],
            [
                'name' => 'Permesso Legge 104',
                'acronym' => 'L104',
            ],
        ];

        foreach ($attendanceTypes as $attendanceType) {
            AttendanceType::create($attendanceType);
        }
    }
}
