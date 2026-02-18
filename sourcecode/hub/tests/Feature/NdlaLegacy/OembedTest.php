<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use App\Models\ContentVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

final class OembedTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    #[TestWith(['https://hub-test-ndla-legacy.edlib.test/oembed'])]
    #[TestWith(['https://hub-test-ndla-legacy.edlib.test/oembed/preview'])]
    public function testOembed(string $endpoint): void
    {
        $edlib2UsageId = $this->faker->uuid;

        Content::factory()
            ->withVersion(ContentVersion::factory()->state([
                'title' => 'My content',
            ]))
            ->edlib2UsageId($edlib2UsageId)
            ->create();

        $this->getJson("$endpoint?url=https%3A%2F%2Fhub-test-ndla-legacy.edlib.test%2Fresource%2F$edlib2UsageId&format=json")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('type', 'rich')
                    ->where('width', 800)
                    ->where('height', 600)
                    ->where('title', 'My content')
                    ->where('version', '1.0')
                    ->where('html', fn(string $html) => str_contains(
                        $html,
                        "src=\"https://hub-test-ndla-legacy.edlib.test/resource/$edlib2UsageId\"",
                    )),
            );
    }

    public function testCanPassLocale(): void
    {
        $id = $this->faker->uuid;

        Content::factory()
            ->withVersion(ContentVersion::factory()->state([
                'title' => 'My content',
            ]))
            ->edlib2UsageId($id)
            ->create();

        $this->getJson("https://hub-test-ndla-legacy.edlib.test/oembed?url=https%3A%2F%2Fhub-test-ndla-legacy.edlib.test%2Fresource%2F$id%3Flocale=nb-NO&format=json")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('type', 'rich')
                    ->where('width', 800)
                    ->where('height', 600)
                    ->where('title', 'My content')
                    ->where('version', '1.0')
                    ->where('html', fn(string $html) => str_contains(
                        $html,
                        "src=\"https://hub-test-ndla-legacy.edlib.test/resource/$id?locale=nb-NO\"",
                    )),
            );
    }
}
