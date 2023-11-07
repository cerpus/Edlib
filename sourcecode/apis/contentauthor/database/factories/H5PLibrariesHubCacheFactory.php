<?php

declare(strict_types=1);

namespace Database\Factories;

use App\H5PLibrariesHubCache;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PLibrariesHubCache>
 */
class H5PLibrariesHubCacheFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'H5P.Foobar',
            'major_version' => 2,
            'minor_version' => 4,
            'patch_version' => 6,
            'h5p_major_version' => 1,
            'h5p_minor_version' => 25,
            'title' => $this->faker->words(3, true),
            'summary' => $this->faker->sentence,
            'description' => $this->faker->sentences(3, true),
            'icon' => $this->faker->url . '/icon.svg',
            'is_recommended' => 0,
            'popularity' => $this->faker->numberBetween(0, 100000),
            'screenshots' => json_encode([
                $this->faker->url . '/image01.jpg',
                $this->faker->url . '/image02.jpg',
                $this->faker->url . '/image03.jpg',
            ]),
            'license' => '{"id":"MIT","attributes":{"useCommercially":true,"modifiable":true,"distributable":true,"sublicensable":true,"canHoldLiable":false,"mustIncludeCopyright":true,"mustIncludeLicense":true}}',
            'example' => $this->faker->url,
            'tutorial' => $this->faker->url,
            'keywords' => json_encode($this->faker->words()),
            'categories' => '["Other"]',
            'owner' => $this->faker->userName,
        ];
    }
}
