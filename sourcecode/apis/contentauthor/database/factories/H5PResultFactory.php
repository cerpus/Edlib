<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => $this->faker->numberBetween(1, 10000),
            'user_id' => $this->faker->uuid,
            'score' => 0,
            'max_score' => 10,
            'opened' => Carbon::now()->subMinutes(1)->timestamp,
            'finished' => Carbon::now()->timestamp,
            'time' => 0,
            'context' => null,
        ];
    }
}
