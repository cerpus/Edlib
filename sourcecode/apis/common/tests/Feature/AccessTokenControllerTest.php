<?php

namespace Tests\Feature;

use App\Models\AccessToken;
use App\Models\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;
use function strlen;

class AccessTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testListByApplication(): void
    {
        /** @var Application $application */
        $application = Application::factory()->create();

        /** @var Collection<AccessToken> $accessTokens */
        $accessTokens = AccessToken::factory([])
            ->for($application)
            ->count(3)
            ->create();

        $this->getJson("/common/applications/{$application->id}/access_tokens")
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonMissingExact(['token' => $accessTokens->get(0)->token]);
    }

    public function testCreate(): void
    {
        /** @var Application $application */
        $application = Application::factory()->create();

        $accessToken = $this->postJson("/common/applications/{$application->id}/access_tokens", [
            'name' => 'My pretty token',
        ])
            ->assertCreated()
            ->assertJson([
                'name' => 'My pretty token',
            ])
            ->json();

        $this->assertArrayHasKey('token', $accessToken);
        $this->assertEquals(48, strlen($accessToken['token']));
    }

    public function testDelete(): void
    {
        /** @var AccessToken $accessToken */
        $accessToken = AccessToken::factory()->create();

        $this->deleteJson("/common/applications/{$accessToken->application_id}/access_tokens/{$accessToken->id}")
            ->assertNoContent();

        $this->assertNull($accessToken->fresh());
    }

    public function testCannotDeleteIfApplicationIdMismatched(): void
    {
        /** @var AccessToken $accessToken */
        $accessToken = AccessToken::factory()->create();

        $this->delete("/common/applications/{$this->faker->uuid}/access_tokens/{$accessToken->id}")
            ->assertNotFound();

        $this->assertNotNull($accessToken->fresh());
    }
}
