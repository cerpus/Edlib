<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class ResourceCopyrightTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[TestWith(['v1', 'v2'])]
    public function testFetchesCopyrightInfo(string $version): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"proxied": "json data"}'),
        ]));
        $stack->push(Middleware::history($history));
        $caClient = new Client(['handler' => $stack]);

        $this->instance(NdlaLegacyConfig::class, new NdlaLegacyConfig(
            domain: 'hub-test-ndla-legacy.edlib.test',
            contentAuthorHost: 'ca.edlib.test',
            contentAuthorClient: $caClient,
            publicKeyOrJwksUri: 'http://localhost/.well-known/jwks.json',
            internalLtiPlatformKey: LtiPlatform::factory()->create()->key,
        ));

        $tag = $this->faker->uuid;

        Content::factory()
            ->tag('edlib2_usage_id:' . $tag)
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withLaunchUrl('https://ca.edlib.test/h5p/12345'),
            )
            ->create();

        $this->getJson('https://hub-test-ndla-legacy.edlib.test/' . $version . '/resource/' . $tag . '/copyright')
            ->assertOk()
            ->assertJson([
                'proxied' => 'json data',
            ]);

        $this->assertCount(1, $history);
        $this->assertSame('h5p/12345/copyright', $history[0]['request']->getUri()->__toString());
    }
}
