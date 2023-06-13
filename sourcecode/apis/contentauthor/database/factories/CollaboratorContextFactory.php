<?php

namespace Database\Factories;

use App\CollaboratorContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<CollaboratorContext>
 */
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
