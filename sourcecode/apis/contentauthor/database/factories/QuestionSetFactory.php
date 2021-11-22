<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionSetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'owner' => $this->faker->uuid,
            'external_reference' => null,
            'language_code' => $this->faker->languageCode,
        ];
    }
}
