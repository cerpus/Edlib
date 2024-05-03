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
        $title = $this->faker->sentence;

        return [
            'content_id' => Content::factory(),
            'published' => $this->faker->boolean,
            'title' => $title,
            'title_html' => $title,
            'lti_tool_id' => LtiTool::factory(),
            'lti_launch_url' => 'https://hub-test.edlib.test/lti/samples/presentation',
            'language_iso_639_3' => $this->faker->randomElement(['eng', 'nob']),
            'license' => $this->faker->randomElement(['CC0-1.0', 'CC-BY-2.5', null]),
        ];
    }

    public function withLaunchUrl(string $launchUrl): self
    {
        return $this->state(['lti_launch_url' => $launchUrl]);
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

    public function withTag(TagFactory $tag): self
    {
        $values = $tag->getRawAttributes(null);
        return $this->hasAttached(
            $tag,
            [
                'verbatim_name' => $values['name'],
            ],
            'tags'
        );
    }
}
