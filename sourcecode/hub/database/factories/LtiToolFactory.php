<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LtiToolEditMode;
use App\Models\LtiTool;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @template-extends Factory<LtiTool>
 */
final class LtiToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(asText: true),
            'lti_version' => '1.1',
            'creator_launch_url' => 'https://hub-test.edlib.test/lti/samples/deep-link',
            'consumer_key' => $this->faker->unique()->word(),
            'consumer_secret' => $this->faker->password(32),
            'send_name' => $this->faker->boolean,
            'send_email' => $this->faker->boolean,
            'slug' => $this->faker->unique()->slug(nbWords: 2),
            'default_published' => $this->faker->boolean,
            'default_shared' => $this->faker->boolean,
        ];
    }

    public function slug(string $slug): self
    {
        return $this->state(['slug' => $slug]);
    }

    public function launchUrl(string $launchUrl): self
    {
        return $this->state(['creator_launch_url' => $launchUrl]);
    }

    public function sendName(bool $sendName = true): self
    {
        return $this->state(['send_name' => $sendName]);
    }

    public function sendEmail(bool $sendEmail = true): self
    {
        return $this->state(['send_email' => $sendEmail]);
    }

    public function withName(string $name): self
    {
        return $this->state(['name' => $name]);
    }

    public function withCredentials(Credentials $credentials): self
    {
        return $this->state([
            'consumer_key' => $credentials->key,
            'consumer_secret' => $credentials->secret,
        ]);
    }

    public function extra(LtiToolExtraFactory $extra): self
    {
        return $this->has($extra, 'extras');
    }

    public function editMode(LtiToolEditMode $editMode): self
    {
        return $this->state(['edit_mode' => $editMode]);
    }

    public function defaultPublished(bool $defaultPublished = true): self
    {
        return $this->state(['default_published' => $defaultPublished]);
    }

    public function defaultShared(bool $defaultShared = true): self
    {
        return $this->state(['default_shared' => $defaultShared]);
    }
}
