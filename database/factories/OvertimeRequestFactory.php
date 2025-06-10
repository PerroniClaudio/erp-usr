<?php

namespace Database\Factories;

use App\Models\OvertimeRequest;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeRequestFactory extends Factory {
    protected $model = OvertimeRequest::class;

    public function definition() {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'date' => $this->faker->date(),
            'time_in' => $this->faker->time('H:i'),
            'time_out' => $this->faker->time('H:i'),
            'hours' => $this->faker->numberBetween(1, 8),
            'status' => 0,
            'batch_id' => $this->faker->uuid(),
        ];
    }
}
