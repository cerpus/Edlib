<?php

namespace Database\Factories;

use App\H5PLibrary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PLibrary>
 */
class H5PLibraryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'H5P.Foobar',
            'title' => $this->faker->words(3, true),
            'major_version' => 1,
            'minor_version' => 2,
            'patch_version' => 3,
            'runnable' => true,
            'fullscreen' => true,
            'embed_types' => 'div',
            'semantics' => '[]',
            'tutorial_url' => 'https://burgerking.com',
        ];
    }
}
