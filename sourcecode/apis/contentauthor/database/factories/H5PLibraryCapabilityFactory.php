<?php

declare(strict_types=1);

namespace Database\Factories;

use App\H5PLibraryCapability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PLibraryCapability>
 */
class H5PLibraryCapabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'library_id' => $this->faker->numberBetween(),
            'name' => 'H5P.Foobar 1.2',
            'score' => 0,
            'enabled' => 1,
        ];
    }
}
