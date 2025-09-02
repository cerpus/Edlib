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
use Tests\TestCase;

final class ResourceInformationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testProvidesInformation(): void
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
            internalLtiPlatformKey: LtiPlatform::factory()->create()->id,
        ));

        $edlib2UsageId = $this->faker->uuid;

        Content::factory()
            ->edlib2UsageId($edlib2UsageId)
            ->withVersion(
                ContentVersion::factory()
                    ->published()
                    ->withLaunchUrl('https://ca.edlib.test/h5p/12345'),
            )
            ->create();

        $this->getJson('https://hub-test-ndla-legacy.edlib.test/v1/resource/' . $edlib2UsageId . '/info')
            ->assertOk()
            ->assertJson([
                'proxied' => 'json data',
                'published' => true,
            ]);

        $this->assertIsArray($history);
        $this->assertCount(1, $history);
        $this->assertSame('h5p/12345/info', $history[0]['request']->getUri()->__toString());
    }
}
