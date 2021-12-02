<?php

namespace Database\Factories;

use App\Models\GdprRequestCompletedStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class GdprRequestCompletedStepFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GdprRequestCompletedStep::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'service_name' => $this->faker->randomAscii,
            'step_name' => $this->faker->randomAscii,
            'message' => $this->faker->randomAscii,
        ];
    }
}
