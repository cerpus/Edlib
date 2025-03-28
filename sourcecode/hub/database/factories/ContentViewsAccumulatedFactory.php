<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentViewSource;
use App\Models\Content;
use App\Models\ContentViewsAccumulated;
use App\Models\LtiPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @template-extends Factory<ContentViewsAccumulated>
 */
class ContentViewsAccumulatedFactory extends Factory
{
    #[Override] public function definition(): array
    {
        $source = $this->faker->randomElement(ContentViewSource::cases());

        return [
            'content_id' => Content::factory(),
            'lti_platform_id' => $source->isLtiPlatform()
                ? LtiPlatform::factory()
                : null,
            'date' => $this->faker->dateTimeBetween('-1 year', 'yesterday midnight')->format('Y-m-d'),
            'hour' => $this->faker->numberBetween(0, 23),
            'view_count' => $this->faker->numberBetween(0, 1000000),
            'source' => $this->faker->randomElement(ContentViewSource::cases()),
        ];
    }
}
