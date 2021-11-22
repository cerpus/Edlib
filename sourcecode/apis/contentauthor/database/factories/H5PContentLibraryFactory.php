<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentLibraryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => $this->faker->numberBetween(),
            'library_id' => $this->faker->numberBetween(),
            'dependency_type' => 'preloaded',
            'weight' => $this->faker->numberBetween(0, 100),
            'drop_css' => 0,
        ];
    }
}
