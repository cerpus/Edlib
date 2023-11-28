<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<ContentVersion>
 */
final class ContentVersionFactory extends Factory
{
    public function definition(): array
    {
        $contentAuthorId = $this->faker->numberBetween(1, 5);
        $title = $this->faker->sentence;

        return [
            'content_id' => Content::factory(),
            'published' => $this->faker->boolean,
            'title' => $title,
            'title_html' => $title,
            'lti_tool_id' => LtiTool::factory(),
            'lti_launch_url' => 'https://ca.edlib.test/lti-content/' . $contentAuthorId,
            'language_iso_639_3' => $this->faker->randomElement(['eng', 'nob']),
            'license' => $this->faker->randomElement(['CC0-1.0', 'CC-BY-2.5', null]),
        ];
    }

    public function tool(LtiToolFactory $tool): self
    {
        return $this->for($tool, 'tool');
    }

    public function published(): self
    {
        return $this->state(['published' => true]);
    }

    public function unpublished(): self
    {
        return $this->state(['published' => false]);
    }
}
