<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LtiPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiPlatform>
 */
final class LtiPlatformFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(asText: true),
            'key' => $this->faker->unique()->uuid,
            'secret' => $this->faker->sha256,
        ];
    }
}
