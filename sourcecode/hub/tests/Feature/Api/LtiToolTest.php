<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\LtiTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class LtiToolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->admin()->create();
        $this->withBasicAuth($user->getApiKey(), $user->getApiSecret());
    }

    public function testShowsLtiTool(): void
    {
        $tool = LtiTool::factory()->create();

        $this->getJson('/api/lti-tools/' . $tool->id)
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('id', $tool->id)
                            ->where('consumer_key', $tool->consumer_key)
                            ->missing('consumer_secret')
                            ->where('deep_linking_url', $tool->creator_launch_url)
                            ->where('edit_mode', $tool->edit_mode->value)
                            ->where('proxies_lti_launches', true)
                            ->where('send_name', $tool->send_name)
                            ->where('send_email', $tool->send_email)
                            ->where('links.self', 'https://hub-test.edlib.test/api/lti-tools/' . $tool->id),
                    ),
            );
    }

    public function testListsLtiTools(): void
    {
        LtiTool::factory()->create();

        $this->getJson('/api/lti-tools')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('data')
                    ->count('data', 1)
                    ->has('meta'),
            );
    }
}
