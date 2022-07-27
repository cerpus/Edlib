<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_at' => $this->faker->unixTime,
            'updated_at' => $this->faker->unixTime,
            'user_id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'library_id' => $this->faker->numberBetween(1, 100),
            'parameters' => json_encode([]),
            'filtered' => "",
            'slug' => $this->faker->slug,
            'embed_type' => 'div',
            'disable' => 0,
            'content_type' => null,
            'author' => null,
            'license' => '',
            'keywords' => null,
            'description' => null,
            'is_private' => true,
            'version_id' => null,
            'max_score' => 0,
            'content_create_mode' => 'unitTest',
            'is_published' => 0,
        ];
    }
}
