<?php

declare(strict_types=1);

namespace Database\Factories;

use App\ContentVersion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentVersion>
 */
class ContentVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'created_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            'content_id' => $this->faker->numberBetween(),
            'content_type' => 'testing',
            'parent_id' => null,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
            'user_id' => $this->faker->numberBetween(),
            'linear_versioning' => false,
        ];
    }
}
