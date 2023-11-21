<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auth_id' => $this->faker->uuid,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
