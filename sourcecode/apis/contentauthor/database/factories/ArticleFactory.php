<?php

namespace Database\Factories;

use App\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => $this->faker->text,
            'owner_id' => $this->faker->uuid,
            'content' => $this->faker->text,
            'original_id' => $this->faker->uuid,
            'parent_id' => null,
            'parent_version_id' => null,
            'version_id' => $this->faker->uuid,
            'license' => '',
        ];
    }

    public function newlyCreated(): self
    {
        return $this->state(function () {
            $id = $this->faker->uuid;

            return [
                'id' => $id,
                'original_id' => $id,
                'version_id' => 1,
            ];
        });
    }
}
