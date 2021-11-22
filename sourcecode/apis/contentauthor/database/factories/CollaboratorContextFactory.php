<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollaboratorContextFactory extends Factory
{
    public function definition(): array
    {
        return [
            'system_id' => $this->faker->uuid,
            'context_id' => $this->faker->uuid,
            'type' => 'user',
            'collaborator_id' => $this->faker->uuid,
            'content_id' => $this->faker->uuid,
            'timestamp' => Carbon::now()->timestamp,
        ];
    }
}
