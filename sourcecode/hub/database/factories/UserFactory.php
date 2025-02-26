<?php

declare(strict_types=1);

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
        $email = Str::slug($name) . '@example.com';

        return [
            'name' => $name,
            'email' => $email,
            'email_verified' => true,
            'password' => Hash::make($this->faker->password),
            'admin' => false,
            'locale' => 'en',
            'debug_mode' => false,
        ];
    }

    public function name(string $name): static
    {
        return $this->state(['name' => $name]);
    }

    public function withEmail(string $email, bool $verified = true): static
    {
        return $this->state([
            'email' => $email,
            'email_verified' => $verified,
        ]);
    }

    public function withPasswordResetToken(): static
    {
        return $this->state([
            'password_reset_token' => hash('xxh128', $this->faker->randomAscii),
        ]);
    }

    public function withGoogleId(): static
    {
        return $this->state(['google_id' => $this->faker->randomAscii]);
    }

    public function withFacebookId(): static
    {
        return $this->state(['facebook_id' => $this->faker->randomAscii]);
    }

    public function admin(): static
    {
        return $this->state(['admin' => true]);
    }
}
