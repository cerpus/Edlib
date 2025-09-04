<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentEdlib2Usage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<ContentEdlib2Usage>
 */
class ContentEdlib2UsageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'edlib2_usage_id' => $this->faker->uuid,
        ];
    }
}
