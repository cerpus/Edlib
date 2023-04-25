<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @template-extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->name;
        $email = Str::slug($name).'@example.com';

        return [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($this->faker->password),
            'admin' => false,
            'locale' => 'en',
        ];
    }

    public function withEmail(string $email): static
    {
        return $this->state(['email' => $email]);
    }

    public function admin(): static
    {
        return $this->state(['admin' => true]);
    }
}
