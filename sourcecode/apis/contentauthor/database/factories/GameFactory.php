<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'gametype' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'language_code' => $this->faker->languageCode,
            'owner' => $this->faker->uuid,
            'game_settings' => json_encode(['setting' => true]),
            'version_id' => null,
            'license' => '',
        ];
    }
}
