<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LtiTool;
use App\Models\LtiToolExtra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiToolExtra>
 */
final class LtiToolExtraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence,
            'lti_tool_id' => LtiTool::factory(),
            'lti_launch_url' => $this->faker->url,
            'admin' => $this->faker->boolean,
            'slug' => $this->faker->unique()->slug(nbWords: 2),
        ];
    }

    public function admin(bool $admin = true): self
    {
        return $this->state(['admin' => $admin]);
    }

    public function slug(string $slug): self
    {
        return $this->state(['slug' => $slug]);
    }
}
