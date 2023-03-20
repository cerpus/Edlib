<?php

namespace Database\Factories;

use App\Models\LtiResource;
use App\Models\LtiTool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiResource>
 */
final class LtiResourceFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'title' => $title,
            'title_html' => $title,
            'lti_tool_id' => LtiTool::factory(),
        ];
    }
}
