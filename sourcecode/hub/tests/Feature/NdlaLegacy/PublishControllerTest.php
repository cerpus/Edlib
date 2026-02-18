<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublishControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testPublishWithId(): void
    {
        $jwt = Jwt::sign([
            'sub' => '12345',
            'https://ndla.no/ndla_id' => '12345',
            'https://ndla.no/user_name' => 'Q. Bernetes',
            'https://ndla.no/user_email' => 'bernetes@ndla.no',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer ' . $jwt)
            ->putJson('https://hub-test-ndla-legacy.edlib.test/v1/resource/12345/publish')
            ->assertOk();

        $this->assertDatabaseHas(User::class, ['ndla_id' => '12345']);
    }

    public function testPublishWithUrl(): void
    {
        $jwt = Jwt::sign([
            'sub' => '12345',
            'https://ndla.no/ndla_id' => '12345',
            'https://ndla.no/user_name' => 'Q. Bernetes',
            'https://ndla.no/user_email' => 'bernetes@ndla.no',
        ]);

        $this
            ->withHeader('Authorization', 'Bearer ' . $jwt)
            ->putJson('https://hub-test-ndla-legacy.edlib.test/v1/resource/12345/publish', [
                'url' => 'https://example.com/my/resource',
            ])
            ->assertOk();
    }

    public function testPublishEndpointRequiresJwt(): void
    {
        $this->putJson('https://hub-test-ndla-legacy.edlib.test/v1/resource/12345/publish', [
            'url' => 'https://example.com/my/resource',
        ])
            ->assertUnauthorized();
    }
}
