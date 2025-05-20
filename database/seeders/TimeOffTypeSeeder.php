<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeOffTypeSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        $types = [
            "Ferie",
            "Rol"
        ];

        foreach ($types as $type) {
            \App\Models\TimeOffType::create([
                'name' => $type,
            ]);
        }
    }
}
