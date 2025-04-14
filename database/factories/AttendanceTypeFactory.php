<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceType>
 */
class AttendanceTypeFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $word = $this->faker->word();
        $acronym = strtoupper(substr($word, 0, 3));

        return [
            //
            'name' => $word,
            'acronym' => $acronym
        ];
    }
}
