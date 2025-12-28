<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\ContentRole;
use App\Models\Content;
use App\Models\Context;
use App\Models\LtiPlatform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

final class ContextTest extends TestCase
{
    use RefreshDatabase;

    public function testListsContexts(): void
    {
        $user = User::factory()->admin()->create();
        $id1 = Context::factory()->name('context_1')->create()->id;
        $id2 = Context::factory()->name('context_2')->create()->id;
        $id3 = Context::factory()->name('context_3')->create()->id;

        $this
            ->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->getJson('https://hub-test.edlib.test/api/contexts')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->where('data', [
                        [
                            'id' => $id1,
                            'name' => 'context_1',
                            'links' => [
                                'self' => 'https://hub-test.edlib.test/api/contexts/context_1',
                            ],
                        ],
                        [
                            'id' => $id2,
                            'name' => 'context_2',
                            'links' => [
                                'self' => 'https://hub-test.edlib.test/api/contexts/context_2',
                            ],
                        ],
                        [
                            'id' => $id3,
                            'name' => 'context_3',
                            'links' => [
                                'self' => 'https://hub-test.edlib.test/api/contexts/context_3',
                            ],
                        ],
                    ])
                    ->where('meta', [
                        'pagination' => [
                            'count' => 3,
                            'current_page' => 1,
                            'links' => [],
                            'per_page' => 100,
                            'total' => 3,
                            'total_pages' => 1,
                        ],
                    ]),
            );
    }

    public function testStoresContext(): void
    {
        $user = User::factory()->admin()->create();

        $this->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->postJson('https://hub-test.edlib.test/api/contexts', [
                'name' => 'my_new_context',
            ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('data.id')
                    ->where('data.name', 'my_new_context'),
            );
    }

    public function testDeletesContext(): void
    {
        $context = Context::factory()->name('to_be_gone')->create();

        // ensure we can delete it even when referenced
        Content::factory()->withContext($context)->create();
        LtiPlatform::factory()->withContext($context, ContentRole::Owner)->create();

        $user = User::factory()->admin()->create();

        $this->withBasicAuth($user->getApiKey(), $user->getApiSecret())
            ->deleteJson('https://hub-test.edlib.test/api/contexts/to_be_gone')
            ->assertNoContent();

        $this->assertDatabaseMissing($context);
    }
}
