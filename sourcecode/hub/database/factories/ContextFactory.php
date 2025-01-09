<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Context;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Context>
 */
final class ContextFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
        ];
    }

    public function name(string $name): self
    {
        return $this->state([
            'name' => $name,
        ]);
    }
}
