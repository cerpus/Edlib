<?php

namespace Database\Factories;

use App\Article;
use App\Libraries\Versioning\VersionableObject;
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
            'version_purpose' => VersionableObject::PURPOSE_INITIAL,
            'license' => '',
        ];
    }
}
