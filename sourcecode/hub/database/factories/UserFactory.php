<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'admin' => true,
        ]);
    }

    public function hasLogin(): static
    {
        return $this->has(UserLogin::factory(), 'login');
    }
}
