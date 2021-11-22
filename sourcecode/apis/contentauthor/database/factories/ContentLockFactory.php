<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContentLockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => $this->faker->uuid,
            'auth_id' => $this->faker->uuid,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
