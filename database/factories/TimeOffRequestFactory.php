<?php

namespace Database\Factories;

use App\Models\TimeOffRequest;
use App\Models\User;
use App\Models\Company;
use App\Models\TimeOffType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeOffRequestFactory extends Factory {
    protected $model = TimeOffRequest::class;

    public function definition(): array {
        $dateFrom = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $dateTo = (clone $dateFrom)->modify('+1 day');
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'time_off_type_id' => TimeOffType::factory(),
            'date_from' => $dateFrom->format('Y-m-d H:i:s'),
            'date_to' => $dateTo->format('Y-m-d H:i:s'),
            'status' => $this->faker->randomElement([0, 1, 2, 3]),
            'batch_id' => $this->faker->uuid(),
        ];
    }
}
