<?php

namespace Database\Factories;

use App\Models\GdprRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class GdprRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GdprRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->randomNumber(),
            'request_id' => $this->faker->randomAscii,
            'user_id' => $this->faker->randomAscii,
        ];
    }
}
