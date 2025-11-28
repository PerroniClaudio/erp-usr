<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory {
    protected $model = Vehicle::class;

    public function definition(): array {
        return [
            'brand' => $this->faker->company(),
            'model' => $this->faker->word(),
            'plate_number' => $this->faker->bothify('??####??'),
            'price_per_km' => $this->faker->randomFloat(4, 0.1, 1.5),
        ];
    }
}
