<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use App\Models\ContentVersion;
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

        $content = Content::factory()
            ->withVersion(ContentVersion::factory()->state([
                'title' => 'My content',
            ]))
            ->tag('edlib2_usage_id:' . $id)
            ->create();

        $this->getJson("$endpoint?url=https%3A%2F%2Fhub-test-ndla-legacy.edlib.test%2Fresource%2F$id&format=json")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertExactJson([
                'type' => 'rich',
                'width' => 800,
                'title' => 'My content',
                'html' => "<div>\n<iframe src=\"https://hub-test.edlib.test/content/{$content->id}/embed\" title=\"My content\" width=\"800\" height=\"600\" allowfullscreen></iframe>\n<script>((f, h) => addEventListener('message', e => f &&\nf.contentWindow === e.source &&\ne.data && e.data.action && e.data.action === 'resize' && e.data[h] &&\n(f.height = String(e.data[h] + f.getBoundingClientRect().height - f[h]))\n))(document.currentScript.previousElementSibling, 'scrollHeight')</script>\n</div>\n",
                'version' => '1.0',
                'height' => 600,
            ]);
    }
}
