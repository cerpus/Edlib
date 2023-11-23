<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentUserRole;
use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<Content>
 */
final class ContentFactory extends Factory
{
    public function definition(): array
    {
        return [
        ];
    }

    public function withUser(
        User|UserFactory $user,
        ContentUserRole|null $role = ContentUserRole::Owner,
    ): self {
        return $this->hasAttached($user, ['role' => $role], 'users');
    }

    public function withVersion(ContentVersionFactory $version): self
    {
        return $this->has($version, 'versions');
    }

    public function withPublishedVersion(): self
    {
        return $this->withVersion(
            ContentVersion::factory()->published(),
        );
    }
}
