<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => $this->faker->uuid,
            'name' => $this->faker->uuid.'.jpg',
            'original_name' => $this->faker->slug(3).'.'.$this->faker->fileExtension,
            'remember_token' => str_random(10),
        ];
    }
}
