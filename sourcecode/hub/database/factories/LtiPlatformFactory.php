<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentRole;
use App\Models\Context;
use App\Models\LtiPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiPlatform>
 */
final class LtiPlatformFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(asText: true),
            'key' => $this->faker->unique()->uuid,
            'secret' => $this->faker->sha256,
            'enable_sso' => true,
            'authorizes_edit' => false,
        ];
    }

    public function name(string $name): self
    {
        return $this->state(['name' => $name]);
    }

    public function withContext(Context|ContextFactory $context, ContentRole $role): self
    {
        return $this->hasAttached($context, ['role' => $role], 'contexts');
    }
}
