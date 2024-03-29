<?php

namespace Database\Factories;

use App\Game;
use App\Gametype;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'gametype' => Gametype::factory(),
            'title' => $this->faker->sentence,
            'language_code' => $this->faker->languageCode,
            'owner' => $this->faker->uuid,
            'game_settings' => json_encode(['setting' => true]),
            'version_id' => null,
            'license' => '',
        ];
    }
}
