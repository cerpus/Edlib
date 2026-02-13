<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentLock;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<ContentLock>
 */
final class ContentLockFactory extends Factory
{
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function lockedAt(DateTimeInterface $timestamp): self
    {
        return $this->state([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
