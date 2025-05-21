<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        //

        $users = [
            ['name' => 'Erasmo Salsano - iFortech', 'email' => 'e.salsano@ifortech.com'],
            ['name' => 'Andrea Fumagalli - iFortech', 'email' => 'a.fumagalli@ifortech.com'],
            ['name' => 'Elena Carlucci - iFortech', 'email' => 'e.carlucci@ifortech.com'],
        ];

        foreach ($users as $user) {
            \App\Models\User::factory()->create([
                'name' => $user['name'],
                'email' => $user['email']
            ]);
        }
    }
}
