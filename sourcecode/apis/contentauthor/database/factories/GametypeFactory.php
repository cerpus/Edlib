<?php

namespace Database\Factories;

use App\Gametype;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Gametype>
 */
class GametypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => 'Game A 1.0',
            'name' => 'CERPUS.GameA',
            'major_version' => 1,
            'minor_version' => 0,
        ];
    }
}
