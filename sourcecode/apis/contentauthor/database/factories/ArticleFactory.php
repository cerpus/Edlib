<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid,
            'title' => $this->faker->text,
            'owner_id' => $this->faker->uuid,
            'content' => $this->faker->text,
            'original_id' => $this->faker->uuid,
            'parent_id' => null,
            'parent_version_id' => null,
            'version_id' => $this->faker->uuid
        ];
    }

    public function newlyCreated(): self
    {
        return $this->state(function () {
            $id = $this->faker->uuid;

            return [
                'id' => $id,
                'original_id' => $id,
                'version_id' => 1,
            ];
        });
    }

    public function published(): self
    {
        return $this->state(fn() => [
            'is_published' => true,
        ]);
    }

    public function unpublished(): self
    {
        return $this->state(fn() => [
            'is_published' => false,
        ]);
    }

    public function listed(): self
    {
        return $this->state(fn() => [
            'is_private' => false,
        ]);
    }

    public function unlisted(): self
    {
        return $this->state(fn() => [
            'is_private' => true,
        ]);
    }
}
