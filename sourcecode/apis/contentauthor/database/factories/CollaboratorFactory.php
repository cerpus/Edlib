<?php

namespace Database\Factories;

use App\Collaborator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Collaborator>
 */
class CollaboratorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
        ];
    }
}
