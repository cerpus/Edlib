<?php

namespace Database\Factories;

use App\Models\LtiRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

class LtiRegistrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LtiRegistration::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomNumber(),
            'issuer' => $this->faker->randomNumber(),
            'client_id' => $this->faker->randomNumber(),
            'platform_login_auth_endpoint' => $this->faker->url(),
            'platform_auth_token_endpoint' => $this->faker->url(),
            'platform_key_set_endpoint' => $this->faker->url(),
        ];
    }
}
