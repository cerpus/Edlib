<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

use App\Models\Content;
use App\Models\Context;
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

        Content::factory()
            ->withPublishedVersion()
            ->withContext(Context::factory()->name('my_context'))
            ->edlib2UsageId('f4d10eb4-a6af-4c18-9736-b16b70959c66')
            ->create();

        $this->withToken($jwt)
            ->postJson('https://hub-test-ndla-legacy.edlib.test/copy', [
                'url' => 'https://hub-test-ndla-legacy.edlib.test/resource/f4d10eb4-a6af-4c18-9736-b16b70959c66',
            ])
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('url');

                $newUsageId = preg_replace(
                    '!^https://hub-test-ndla-legacy.edlib.test/resource/([0-9a-f]{8}(-[0-9a-f]{4}){3}-[0-9a-f]{12}).*!',
                    '$1',
                    $json->toArray()['url'],
                );
                $this->assertNotSame('f4d10eb4-a6af-4c18-9736-b16b70959c66', $newUsageId);

                $copiedContent = Content::firstWithEdlib2UsageIdOrFail($newUsageId);

                $this->assertSame(
                    ['my_context'],
                    $copiedContent->contexts
                        ->map(fn(Context $context) => $context->name)
                        ->toArray(),
                );

                $this->assertSame(
                    'https://hub-test-ndla-legacy.edlib.test/resource/' . $newUsageId,
                    $json->toArray()['url'],
                );
            });
    }
}
