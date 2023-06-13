<?php

namespace Database\Factories;

use App\H5PCollaborator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<H5PCollaborator>
 */
class H5PCollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'h5p_id' => $this->faker->numberBetween(1, 10000),
            'email' => $this->faker->email,
        ];
    }
}
