<?php

declare(strict_types=1);

namespace Database\Factories;

use App\H5PLibraryLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PLibraryLanguage>
 */
class H5PLibraryLanguageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'library_id' => $this->faker->numberBetween(),
            'language_code' => $this->faker->unique()->languageCode(),
            'translation' => '',
        ];
    }
}
