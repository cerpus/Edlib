<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class CopyTest extends TestCase
{
    use RefreshDatabase;

    public function testCopiesContent(): void
    {
        $jwt = Jwt::sign([
            'https://ndla.no/user_name' => 'Bob',
            'https://ndla.no/user_email' => 'bob@example.com',
            'https://ndla.no/ndla_id' => '89w7tg87as8g78a7s8',
            'exp' => time() + 600,
            'scope' => 'openid profile email',
        ]);

        $content = Content::factory()
            ->withPublishedVersion()
            ->tag('edlib2_usage_id:f4d10eb4-a6af-4c18-9736-b16b70959c66')
            ->create();

        $this->withToken($jwt)
            ->postJson('https://hub-test-ndla-legacy.edlib.test/copy', [
                'url' => 'https://hub-test-ndla-legacy.edlib.test/resource/f4d10eb4-a6af-4c18-9736-b16b70959c66',
            ])
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($content) {
                $json->has('url')->where('url', function (string $url) use ($content) {
                    $expectedId = Content::where('id', '<>', $content->id)
                        ->firstOrFail()
                        ->tags()
                        ->where('prefix', 'edlib2_usage_id')
                        ->firstOrFail()
                        ->name;

                    return $url === 'https://hub-test-ndla-legacy.edlib.test/resource/' . $expectedId;
                });
            });
    }
}
