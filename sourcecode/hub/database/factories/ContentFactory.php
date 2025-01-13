<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentRole;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentView;
use App\Models\Context;
use App\Models\Tag;
use App\Models\User;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

use function is_string;

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

    public function tag(TagFactory|string $tag, string|null $verbatimName = null): self
    {
        if (is_string($tag)) {
            ['name' => $name, 'prefix' => $prefix] = Tag::parse($tag);

            $tag = Tag::factory(null, [
                'name' => $name,
                'prefix' => $prefix,
            ]);
        }

        return $this->hasAttached($tag, [
            'verbatim_name' => $verbatimName,
        ], 'tags');
    }

    public function trashed(DateTimeInterface|null $deletedAt = null): self
    {
        return $this->state([
            'deleted_at' => DateTimeImmutable::createFromInterface(
                $deletedAt ?? $this->faker->dateTime,
            ),
        ]);
    }

    public function withUser(
        User|UserFactory $user,
        ContentRole|null $role = ContentRole::Owner,
    ): self {
        return $this->hasAttached($user, ['role' => $role], 'users');
    }

    public function withContext(Context|ContextFactory $context): self
    {
        return $this->hasAttached($context, [], 'contexts');
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
