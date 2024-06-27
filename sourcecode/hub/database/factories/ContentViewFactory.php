<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentViewSource;
use App\Models\Content;
use App\Models\ContentView;
use App\Models\LtiPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @template-extends Factory<ContentView>
 */
final class ContentViewFactory extends Factory
{
    #[Override] public function definition(): array
    {
        $source = $this->faker->randomElement(ContentViewSource::cases());
        $ipLottery = $this->faker->randomElement([4, 6, null]);

        return [
            'content_id' => Content::factory(),
            'lti_platform_id' => $source->isLtiPlatform()
                ? LtiPlatform::factory()
                : null,
            'source' => $source,
            'ip' => match ($ipLottery) {
                6 => $this->faker->ipv6,
                4 => $this->faker->ipv4,
                default => null,
            },
        ];
    }
}