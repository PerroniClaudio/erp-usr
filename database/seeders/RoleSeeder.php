<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;


class RoleSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $roles = [
            'admin',
            'Responsabile HR',
            'Operatore HR',
            'standard',
        ];

        collect($roles)->each(function (string $role) {
            Role::firstOrCreate(['name' => $role]);
        });
    }
}
