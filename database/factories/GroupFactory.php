<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory {
    protected $model = Group::class;

    public function definition(): array {
        return [
            'name' => $this->faker->company() . ' Group',
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
