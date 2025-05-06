<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\TimeOffRequest;
use App\Models\TimeOffType;
use Illuminate\Support\Facades\DB;

Route::get('/test-time-off', function () {

    DB::transaction(function () {
        $type = TimeOffType::create([
            'name' => 'Test Time Off Type',
        ]);

        for ($i = 0; $i < 3; $i++) {

            $newUser = User::create([
                'name' => 'Test User ' . $i,
                'email' => 'testuser' . $i . '@example.com',
                'password' => bcrypt('password'),
            ]);

            for ($j = 1; $j <= 3; $j++) {

                $start_date = fake()->dateTimeBetween('now', '+1 month');
                $end_date = (clone $start_date)->modify('+' . rand(1, 5) . ' days');

                TimeOffRequest::create([
                    'user_id' => $newUser->id,
                    'company_id' => 1,
                    'time_off_type_id' => 1,
                    'status' => 2,
                    'date_from' => $start_date,
                    'date_to' => $end_date,
                    'batch_id' => $i
                ]);
            }
        }
    });
});
