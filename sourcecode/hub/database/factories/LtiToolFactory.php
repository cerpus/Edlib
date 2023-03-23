<?php

namespace Database\Factories;

use App\Models\LtiTool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiTool>
 */
final class LtiToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(asText: true),
            'lti_version' => $this->faker->randomElement(['1.1', '1.3']),
            'creator_launch_url' => $this->faker->url,
        ];
    }
}
