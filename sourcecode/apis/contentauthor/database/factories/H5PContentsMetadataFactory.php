<?php

namespace Database\Factories;

use App\H5PContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentsMetadataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(1, 10000),
            'content_id' => H5PContent::factory()->create()->id,
            'authors' => '[]',
            'source' => null,
            'year_from' => null,
            'year_to' => null,
            'license' => null,
            'license_version' => null,
            'license_extras' => null,
            'author_comments' => null,
            'changes' => '[]',
        ];
    }
}
