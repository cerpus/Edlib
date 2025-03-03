<?php

namespace Database\Factories;

use App\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @template-extends Factory<File>
 */
class FileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => $this->faker->uuid,
            'name' => $this->faker->uuid . '.jpg',
            'original_name' => $this->faker->slug(3) . '.' . $this->faker->fileExtension(),
            'remember_token' => Str::random(10),
        ];
    }
}
