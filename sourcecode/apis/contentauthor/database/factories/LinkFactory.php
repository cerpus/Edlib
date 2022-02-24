<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'link_url' => $this->faker->url,
            'link_type' => 'external_link',
            'link_text' => $this->faker->sentence,
            'owner_id' => $this->faker->uuid,
            'license' => '',
        ];
    }
}
