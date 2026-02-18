<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Tag>
 */
class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'prefix' => $this->faker->word,
            'name' => strtolower($this->faker->word . '.' . $this->faker->word),
        ];
    }
}
