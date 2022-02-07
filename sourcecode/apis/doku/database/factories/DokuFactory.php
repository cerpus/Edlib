<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DokuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'creator_id' => $this->faker->uuid,
            'draft' => $this->faker->boolean,
            'public' => $this->faker->boolean,
            'data' => [
                $this->faker->word => $this->faker->word,
                $this->faker->word => $this->faker->sentence,
            ],
        ];
    }
}
