<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiResource;
use App\Models\LtiTool;
use App\Models\LtiVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Content>
 */
final class ContentFactory extends Factory
{
    public function definition(): array
    {
        return [
        ];
    }

    public function withPublishedVersion(): static
    {
        return $this->has(
            ContentVersion::factory()
                ->state(['published' => true])
                ->for(
                    LtiResource::factory()->for(
                        LtiTool::factory()->state([
                            'lti_version' => LtiVersion::Lti1_1,
                        ]),
                        'tool',
                    ),
                    'resource',
                ),
            'versions',
        );
    }
}
