<?php

namespace Database\Factories;

use App\H5PContentsUserData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PContentsUserData>
 */
class H5PContentsUserDataFactory extends Factory
{
    public function definition(): array
    {
        return [
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
