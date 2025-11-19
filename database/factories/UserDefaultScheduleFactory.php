<?php

namespace Database\Factories;

use App\Models\AttendanceType;
use App\Models\User;
use App\Models\UserDefaultSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDefaultScheduleFactory extends Factory
{
    protected $model = UserDefaultSchedule::class;

    public function definition(): array
    {
        $day = $this->faker->randomElement(UserDefaultSchedule::DAYS);
        $start = $this->faker->numberBetween(6, 10);
        $end = $start + $this->faker->numberBetween(4, 8);

        return [
            'user_id' => User::factory(),
            'day' => $day,
            'hour_start' => sprintf('%02d:00', $start),
            'hour_end' => sprintf('%02d:00', $end),
            'total_hours' => $end - $start,
            'attendance_type_id' => AttendanceType::factory(),
        ];
    }
}
