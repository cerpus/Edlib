<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleCollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => $this->faker->uuid,
            'email' => $this->faker->email,
        ];
    }
}
