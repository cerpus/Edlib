<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentUserRole;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentView;
use App\Models\User;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Content>
 */
final class ContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shared' => $this->faker->boolean,
        ];
    }

    public function shared(bool $shared = true): self
    {
        return $this->state(['shared' => $shared]);
    }

    public function trashed(DateTimeInterface|null $deletedAt = null): self
    {
        return $this->state([
            'deleted_at' => DateTimeImmutable::createFromInterface(
                $deletedAt ?? $this->faker->dateTime
            ),
        ]);
    }

    public function withUser(
        User|UserFactory $user,
        ContentUserRole|null $role = ContentUserRole::Owner,
    ): self {
        return $this->hasAttached($user, ['role' => $role], 'users');
    }

    public function withVersion(ContentVersionFactory|null $version = null): self
    {
        $version ??= ContentVersion::factory();

        return $this->has($version, 'versions');
    }

    public function withPublishedVersion(): self
    {
        return $this->withVersion(
            ContentVersion::factory()->published(),
        );
    }

    public function withView(ContentViewFactory|null $view = null): self
    {
        $view ??= ContentView::factory();

        return $this->has($view, 'views');
    }
}
