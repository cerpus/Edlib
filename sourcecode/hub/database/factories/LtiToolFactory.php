<?php

declare(strict_types=1);

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
            'lti_version' => '1.1',
            'creator_launch_url' => $this->faker->url,
            'consumer_key' => $this->faker->unique()->word(),
            'consumer_secret' => $this->faker->password(32),
        ];
    }

    public function withName(string $name): self
    {
        return $this->state(['name' => $name]);
    }
}
