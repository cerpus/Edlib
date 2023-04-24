<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<ContentVersion>
 */
final class ContentVersionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'lti_resource_id' => LtiResource::factory(),
            'published' => $this->faker->boolean,
        ];
    }

    public function unpublished(): self
    {
        return $this->state([
            'published' => false,
        ]);
    }
}
