<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
        $id = $this->faker->uuid;

        Content::factory()
            ->withPublishedVersion()
            ->tag('edlib2_usage_id:' . $id)
            ->create();

        $this->getJson("$endpoint?url=https%3A%2F%2Fhub-test-ndla-legacy.edlib.test%2Fresource%2F$id&format=json")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJson([
                'type' => 'rich',
                'width' => 800,
                'height' => 600,
            ]);
    }
}
