<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @template-extends Factory<UserLogin>
 */
class UserLoginFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->name;
        $email = Str::slug($name).'@example.com';

        return [
            'email' => $email,
            'user_id' => User::factory()->state([
                'name' => $name,
            ]),
            'password' => Hash::make($this->faker->password),
        ];
    }
}
