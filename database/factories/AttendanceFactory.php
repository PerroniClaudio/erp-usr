<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Company;
use App\Models\AttendanceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory {
    protected $model = Attendance::class;

    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'date' => $this->faker->date(),
            'time_in' => $this->faker->time('H:i'),
            'time_out' => $this->faker->time('H:i'),
            'attendance_type_id' => AttendanceType::factory(),
            'hours' => $this->faker->randomFloat(2, 1, 8),
        ];
    }
}
