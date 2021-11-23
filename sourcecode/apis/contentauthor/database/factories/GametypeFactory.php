<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GametypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => 'Game A 1.0',
            'name' => 'CERPUS.GameA',
            'major_version' => 1,
            'minor_version' => 0,
        ];
    }
}
