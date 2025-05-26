<?php

namespace Database\Factories;

use App\Models\BusinessTripExpense;
use App\Models\BusinessTrip;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessTripExpenseFactory extends Factory {
    protected $model = BusinessTripExpense::class;

    public function definition(): array {
        return [
            'business_trip_id' => BusinessTrip::factory(),
            'company_id' => Company::factory(),
            'payment_type' => $this->faker->randomElement(['0', '1', '2', '3', '4', '5']),
            'expense_type' => $this->faker->randomElement(['0', '1', '2', '3']),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'date' => $this->faker->dateTimeThisYear(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ];
    }
}
