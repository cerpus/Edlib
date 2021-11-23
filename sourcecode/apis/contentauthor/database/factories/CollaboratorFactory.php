<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
        ];
    }
}
