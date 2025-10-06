<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FailedAttendance>
 */
class FailedAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            'status' => fake()->randomElement([0, 1, 2, 3]), // 0=da giustificare, 1=in revisione, 2=approvata, 3=rifiutata
            'reason' => fake()->optional()->sentence(),
            'requested_type' => fake()->optional()->numberBetween(1, 5),
            'requested_hours' => fake()->randomFloat(1, 1, 8),
            'request_type' => fake()->randomElement([0, 1]), // 0=permesso, 1=presenza
            'requested_time_in_morning' => fake()->optional()->time('H:i'),
            'requested_time_out_morning' => fake()->optional()->time('H:i'),
            'requested_time_in_afternoon' => fake()->optional()->time('H:i'),
            'requested_time_out_afternoon' => fake()->optional()->time('H:i'),
        ];
    }
}
