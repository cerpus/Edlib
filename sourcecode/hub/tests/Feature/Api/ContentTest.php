<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ContentView;
use App\Models\LtiTool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Symfony\Component\Uid\Ulid;
use Tests\TestCase;

final class ContentTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->admin()->create();
        $this->withBasicAuth($user->getApiKey(), $user->getApiSecret());
    }

    public function testCannotListContentWithoutAdminPermissions(): void
    {
        $nonAdmin = User::factory()->create();

        $this
            ->withBasicAuth($nonAdmin->getApiKey(), $nonAdmin->getApiSecret())
            ->getJson('/api/contents')
            ->assertForbidden();
    }

    public function testListsEmptyContentIndex(): void
    {
        $this->getJson('/api/contents')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('data')
                    ->count('data', 0)
                    ->has('meta'),
            );
    }

    public function testListsContent(): void
    {
        $content = Content::factory()
            ->withVersion()
            ->count(3)
            ->create()
            ->firstOrFail()
            ->refresh();

        $version = $content->latestVersion ?? $this->fail();

        $this->getJson('/api/contents')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 3)
                    ->has(
                        'data.0.versions.data.0',
                        fn(AssertableJson $json) => $json
                            ->where('id', $version->id)
                            ->where('content_id', $version->content_id)
                            ->where('lti_tool_id', $version->lti_tool_id)
                            ->where('edited_by', $version->edited_by)
                            ->where('created_at', $version->created_at?->format('c'))
                            ->where('lti_launch_url', $version->lti_launch_url)
                            ->where('title', $version->title)
                            ->where('language_iso_639_3', $version->language_iso_639_3)
                            ->where('license', $version->license)
                            ->where('published', $version->published)
                            ->where('links.lti_tool', 'https://hub-test.edlib.test/api/lti-tools/' . $version->lti_tool_id)
                            ->where('tags', ['data' => []])
                            ->where('min_score', '0.00')
                            ->where('max_score', '0.00'),
                    )
                    ->has(
                        'meta',
                        fn(AssertableJson $json) => $json
                            ->where('pagination.total', 3),
                    ),
            );
    }

    public function testListsContentsByTag(): void
    {
        $taggedContent = Content::factory()
            ->tag('correct:tag')
            ->withPublishedVersion()
            ->create();

        Content::factory()
            ->tag('wrong:tag')
            ->withPublishedVersion()
            ->create();

        // untagged
        Content::factory()->withPublishedVersion()->create();

        $this->getJson('/api/contents/by_tag/correct%3Atag')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->count('data', 1)
                    ->where('data.0.id', $taggedContent->id)
                    ->has('meta'),
            );
    }

    public function testPaginatesContent(): void
    {
        Content::factory()
            ->withPublishedVersion()
            ->count(49)
            ->create()
            ->fresh();

        $nextUrl = $this->getJson('/api/contents')
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('data')
                    ->whereType('meta.pagination.links.next', 'string'),
            )
            ->json('meta.pagination.links.next');

        $this->getJson($nextUrl)
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has('data')
                    ->count('data', 1)
                    ->has('meta'),
            );
    }

    public function testShowsContent(): void
    {
        $content = Content::factory()->withVersion()->create();

        $this->getJson('/api/contents/' . $content->id)
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('id', $content->id)
                            ->etc(),
                    ),
            );
    }

    public function testStoresContent(): void
    {
        $owner = User::factory()->create();

        $data = [
            'created_at' => $this->faker->dateTime->format('c'),
            'shared' => $this->faker->boolean,
            'roles' => [
                [
                    'user' => $owner->id,
                    'role' => 'owner',
                ],
            ],
        ];

        $this->postJson('/api/contents', $data)
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('id', function (string $id) use ($data) {
                                $idTimestamp = Ulid::fromString($id)
                                    ->getDateTime()
                                    ->format('c');

                                return $idTimestamp === $data['created_at'];
                            })
                            ->where('created_at', $data['created_at'])
                            ->has('updated_at')
                            ->where('deleted_at', null)
                            ->where('shared', $data['shared'])
                            ->has('links.self')
                            ->has('versions')
                            ->where('roles', [
                                'data' => [
                                    [
                                        'user_id' => $owner->id,
                                        'role' => 'owner',
                                    ],
                                ],
                            ]),
                    ),
            );
    }

    public function testStoresContentVersion(): void
    {
        $editor = User::factory()->create();
        $content = Content::factory()->create();
        $tool = LtiTool::factory()->create();

        $data = [
            'lti_tool_id' => $tool->id,
            'lti_launch_url' => $this->faker->url,
            'title' => $this->faker->sentence,
            'published' => $this->faker->boolean,
            'license' => $this->faker->randomElement(['CC0', 'MIT']),
            'language_iso_639_3' => $this->faker->randomElement(['nob', 'eng']),
            'min_score' => '1.00',
            'max_score' => '2.00',
            'tags' => ['h5p:H5P.CoursePresentation'],
            'edited_by' => $editor->id,
        ];

        $this->postJson('/api/contents/' . $content->id . '/versions', $data)
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->has('id')
                            ->where('content_id', $content->id)
                            ->where('lti_tool_id', $data['lti_tool_id'])
                            ->where('edited_by', $data['edited_by'])
                            ->where('lti_launch_url', $data['lti_launch_url'])
                            ->where('title', $data['title'])
                            ->where('license', $data['license'])
                            ->where('language_iso_639_3', $data['language_iso_639_3'])
                            ->where('published', $data['published'])
                            ->where('min_score', $data['min_score'])
                            ->where('max_score', $data['max_score'])
                            ->where('tags', [
                                'data' => [
                                    [
                                        'prefix' => 'h5p',
                                        'name' => 'h5p.coursepresentation',
                                        'verbatim_name' => 'H5P.CoursePresentation',
                                    ],
                                ],
                            ])
                            ->has('created_at')
                            ->has('links.lti_tool'),
                    ),
            );
    }

    public function testShowsVersion(): void
    {
        $content = Content::factory()->withVersion()->create();
        $version = $content->latestVersion ?? $this->fail();

        $this->getJson('/api/contents/' . $content->id . '/versions/' . $version->id)
            ->assertOk()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->where('id', $version->id)
                            ->etc(),
                    ),
            );
    }

    public function testAddsVersion(): void
    {
        $content = Content::factory()
            ->withVersion()
            ->create();

        $data = [
            'title' => 'The new title',
            'lti_launch_url' => $this->faker->url,
            'lti_tool_id' => $content->latestVersion->lti_tool_id ?? $this->fail(),
        ];

        $this->postJson('/api/contents/' . $content->id . '/versions', $data)
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->has('id')
                            ->where('content_id', $content->id)
                            ->where('lti_tool_id', $data['lti_tool_id'])
                            ->where('edited_by', null)
                            ->where('title', $data['title'])
                            ->where('lti_launch_url', $data['lti_launch_url'])
                            ->has('created_at')
                            ->has('license')
                            ->has('language_iso_639_3')
                            ->has('published')
                            ->has('min_score')
                            ->has('max_score')
                            ->where('tags', ['data' => []])
                            ->where('links.lti_tool', 'https://hub-test.edlib.test/api/lti-tools/' . $data['lti_tool_id']),
                    ),
            );
    }

    public function testAddsDeletedContent(): void
    {
        $data = $this->postJson('/api/contents', [
            'deleted_at' => '2024-08-01T00:00:00Z',
        ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->has('id')
                            ->where('deleted_at', '2024-08-01T00:00:00+00:00')
                            ->etc(),
                    )
                    ->etc(),
            )
            ->json();

        $content = Content::withTrashed()
            ->withoutGlobalScope('atLeastOneVersion')
            ->where('id', $data['data']['id'])
            ->firstOrFail();

        $this->assertTrue($content->trashed());
    }

    public function testAddsVersionToDeletedContent(): void
    {
        $ltiTool = LtiTool::factory()->create();
        $content = Content::factory()->trashed()->create();

        $this->postJson('/api/contents/' . $content->id . '/versions', [
            'title' => 'My deleted content',
            'lti_launch_url' => 'https://example.com/',
            'lti_tool_id' => $ltiTool->id,
        ])
            ->assertCreated()
            ->assertJson(
                fn(AssertableJson $json) => $json
                    ->has(
                        'data',
                        fn(AssertableJson $json) => $json
                            ->has('id')
                            ->where('title', 'My deleted content')
                            ->etc(),
                    )
                    ->etc(),
            );

        $this->assertDatabaseHas(ContentVersion::class, [
            'title' => 'My deleted content',
        ]);
    }

    public function testDeletesContent(): void
    {
        $user = User::factory()->create();
        $content = Content::factory()
            ->withUser($user)
            ->withPublishedVersion()
            ->create();

        $this->assertFalse($content->refresh()->trashed());

        $this->deleteJson('/api/contents/' . $content->id)
            ->assertNoContent();

        $this->assertTrue($content->refresh()->trashed());
        $this->assertModelExists($user); // related model not deleted
    }

    public function testDeletesVersions(): void
    {
        $content = Content::factory()
            ->withVersion()
            ->withVersion()
            ->create();

        $versionToDelete = $content->latestVersion ?? $this->fail();

        $this->assertSame(2, $content->versions()->count());

        $this->deleteJson('/api/contents/' . $content->id . '/versions/' . $versionToDelete->id)
            ->assertNoContent();

        $this->assertSame(1, $content->versions()->count());
    }

    public function testListsViews(): void
    {
        $content = Content::factory()
            ->withView(ContentView::factory()->count(5))
            ->create();

        $this->getJson('/api/contents/' . $content->id . '/views')
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }
}
