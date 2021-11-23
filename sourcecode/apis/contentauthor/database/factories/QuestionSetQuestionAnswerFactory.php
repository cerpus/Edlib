<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionSetQuestionAnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'question_id' => null,
            'answer_text' => $this->faker->sentence,
            'correct' => $this->faker->boolean,
            'image' => null,
            'order' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
