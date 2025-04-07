<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentViewSource;
use App\Models\Content;
use App\Models\ContentViewsAccumulated;
use App\Models\LtiPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;
use InvalidArgumentException;
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

    public function source(ContentViewSource $source, LtiPlatformFactory|null $ltiPlatform = null): self
    {
        if ($source->isLtiPlatform() && $ltiPlatform === null) {
            throw new InvalidArgumentException(
                '$ltiPlatform must be provided with ContentViewSource::LtiPlatform',
            );
        }

        if (!$source->isLtiPlatform() && $ltiPlatform !== null) {
            throw new InvalidArgumentException(
                '$ltiPlatform must only be provided with ContentViewSource::LtiPlatform',
            );
        }

        return $this->state([
            'source' => $source,
            'lti_platform_id' => $ltiPlatform,
        ]);
    }

    public function viewCount(int $viewCount): self
    {
        return $this->state([
            'view_count' => $viewCount,
        ]);
    }

    /**
     * @param int<0, 23> $hour
     */
    public function dateAndHour(string $date, int $hour): self
    {
        return $this->state([
            'date' => $date,
            'hour' => $hour,
        ]);
    }
}
