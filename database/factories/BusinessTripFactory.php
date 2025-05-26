<?php

namespace Database\Factories;

use App\Models\BusinessTrip;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessTripFactory extends Factory {
    protected $model = BusinessTrip::class;

    public function definition(): array {
        $dateFrom = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $dateTo = (clone $dateFrom)->modify('+1 day');
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d'),
            'status' => $this->faker->randomElement([0, 1]),
            'code' => $this->faker->unique()->numerify('TRA#####'),
        ];
    }
}
