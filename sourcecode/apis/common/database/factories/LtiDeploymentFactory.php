<?php

namespace Database\Factories;

use App\Models\LtiDeployment;
use Illuminate\Database\Eloquent\Factories\Factory;

class LtiDeploymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LtiDeployment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'deployment_id' => $this->faker->randomNumber(),
        ];
    }
}
