<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionSetQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'question_set_id' => null,
            'question_text' => $this->faker->sentence,
            'image' => null,
            'order' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
