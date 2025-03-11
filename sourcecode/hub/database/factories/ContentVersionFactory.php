<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiTool;
use App\Models\Tag;
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

    public function title(string $title): self
    {
        return $this->state(['title' => $title]);
    }

    public function withLaunchUrl(string $launchUrl): self
    {
        return $this->state(['lti_launch_url' => $launchUrl]);
    }

    public function tool(LtiToolFactory $tool): self
    {
        return $this->for($tool, 'tool');
    }

    public function published(bool $published = true): self
    {
        return $this->state(['published' => $published]);
    }

    public function unpublished(bool $unpublished = true): self
    {
        return $this->state(['published' => !$unpublished]);
    }

    public function withTag(TagFactory|Tag|string $tag, string|null $verbatimName = null): self
    {
        if (is_string($tag)) {
            ['name' => $name, 'prefix' => $prefix] = Tag::parse($tag);

            $tag = Tag::factory(null, [
                'name' => $name,
                'prefix' => $prefix,
            ]);
        }

        return $this->hasAttached($tag, [
            'verbatim_name' => $verbatimName,
        ], 'tags');
    }
}
