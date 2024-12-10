<?php

namespace Tests\Integration\Http\Controllers;

use App\Events\LinkWasSaved;
use App\Http\Controllers\LinkController;
use App\Http\Libraries\License;
use App\Link;
use Faker\Provider\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCreate(): void
    {
        $this->session([
            'authId' => Uuid::uuid(),
        ]);
        $request = Request::create('', parameters: [
            'redirectToken' => 'UniqueToken',
        ]);
        $linkController = app(LinkController::class);

        $response = $linkController->create($request);
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();

        $this->assertCount(10, $data['licenses']);
        $this->assertEquals(License::LICENSE_EDLIB, $data['license']);
        $this->assertFalse($data['isPublished']);
    }

    public function testEdit(): void
    {
        $userId = $this->faker->uuid;
        $this->session([
            'authId' => $userId,
        ]);
        $link = Link::factory()->create([
            'license' => License::LICENSE_BY_NC_ND,
            'owner_id' => $userId,
        ]);

        $request = Request::create('', parameters: [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);

        $linkController = app(LinkController::class);
        $result = $linkController->edit($request, $link->getId());

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertArrayHasKey('licenses', $data);
        $this->assertCount(10, $data['licenses']);
        $this->assertArrayHasKey('license', $data);
        $this->assertEquals(License::LICENSE_BY_NC_ND, $data['license']);
    }

    public function testStore(): void
    {
        Event::fake();
        $this->withSession([
            'authId' => Uuid::uuid(),
        ]);

        $response = $this->post(route('link.store'), [
            'linkType' => 'external_link',
            'linkUrl' => $this->faker->url,
            'linkText' => 'The link',
            'linkMetadata' => json_encode(['title' => 'Another title']),
            'license' => License::LICENSE_BY_NC,
        ])
            ->assertCreated();

        $this->assertDatabaseHas('links', [
            'title' => 'Another title',
            'link_text' => 'The link',
            'license' => License::LICENSE_BY_NC,
        ]);

        /** @var Link $article */
        $article = Link::where('license', License::LICENSE_BY_NC)->first();
        $response->assertJson([
            'url' => route('link.edit', $article->id),
        ]);

        Event::assertDispatched(LinkWasSaved::class);
    }

    public function testUpdate(): void
    {
        Event::fake();
        $userId = $this->faker->uuid;
        $this->session([
            'authId' => $userId,
        ]);

        $link = Link::factory()->create([
            'link_type' => 'external_link',
            'link_url' => 'https://nowhere.not',
            'link_text' => 'The link',
            'title' => 'No title',
            'metadata' => json_encode(['title' => 'No title']),
            'license' => License::LICENSE_BY_NC,
            'owner_id' => $userId,
        ]);

        $response = $this->call('patch', '/link/' . $link->getId(), [
            'linkType' => 'external_link',
            'linkUrl' => 'https://somewhere.not',
            'linkText' => 'Different link',
            'linkMetadata' => json_encode(['title' => 'Another title']),
            'license' => License::LICENSE_BY_NC_SA,
        ])
            ->assertOk();

        $this->assertDatabaseHas('links', [
            'title' => 'Another title',
            'link_text' => 'Different link',
            'link_url' => 'https://somewhere.not',
            'license' => License::LICENSE_BY_NC_SA,
        ]);

        /** @var Link $newLink */
        $newLink = Link::where('title', 'Another title')->first();

        $response->assertJson([
            'url' => route("link.edit", ['link' => $newLink->getId()]),
        ]);

        Event::assertDispatched(LinkWasSaved::class);
    }
}
