<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class H5PCollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'h5p_id' => $this->faker->numberBetween(1, 10000),
            'email' => $this->faker->email,
        ];
    }
}
