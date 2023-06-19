<?php

namespace Database\Factories;

use App\QuestionSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<QuestionSet>
 */
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
            'license' => '',
        ];
    }
}
