<?php

namespace Database\Factories;

use App\ArticleCollaborator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<ArticleCollaborator>
 */
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
