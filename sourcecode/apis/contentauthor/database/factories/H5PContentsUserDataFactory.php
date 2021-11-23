<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentsUserDataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'content_id' => $this->faker->numberBetween(),
            'user_id' => $this->faker->unique()->uuid,
            'sub_content_id' => 0,
            'data_id' => 'state',
            'data' => null,
            'preload' => 1,
            'invalidate' => 1,
            'updated_at' => $this->faker->unixTime,
            'context' => $this->faker->unique()->uuid,
        ];
    }
}
