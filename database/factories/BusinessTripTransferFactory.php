<?php

namespace Database\Factories;

use App\Models\BusinessTripTransfer;
use App\Models\BusinessTrip;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessTripTransferFactory extends Factory {
    protected $model = BusinessTripTransfer::class;

    public function definition(): array {
        return [
            'business_trip_id' => BusinessTrip::factory(),
            'company_id' => Company::factory(),
            'date' => $this->faker->dateTimeThisYear(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'province' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'vehicle_id' => null,
        ];
    }
}
