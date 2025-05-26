<?php

namespace Database\Factories;

use App\Models\TimeOffType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeOffTypeFactory extends Factory {
    protected $model = TimeOffType::class;

    public function definition(): array {
        return [
            'name' => $this->faker->randomElement(['Ferie', 'Rol', 'Permesso', 'Malattia']),
        ];
    }
}
