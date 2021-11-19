<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class H5PContentsVideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->numberBetween(),
            'h5p_content_id' => $this->faker->numberBetween(),
            'video_id' => $this->faker->uuid,
            'source_file' => 'videos/tmp_'.str_replace("-", "", substr($this->faker->uuid, rand(0, 15), 20)).'.mp4',
        ];
    }
}
