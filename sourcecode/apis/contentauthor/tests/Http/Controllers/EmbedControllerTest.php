<?php

namespace Tests\Http\Controllers;

use App\Http\Controllers\EmbedController;
use App\Http\Libraries\License;
use Faker\Provider\Uuid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class EmbedControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCreate(): void
    {
        $this->session([
            'authId' => Uuid::uuid(),
        ]);
        $request = new Request([], [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.local/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);
        /** @var EmbedController $articleController */
        $articleController = app(EmbedController::class);
        $result = $articleController->create($request);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('licenses', $data);
        $this->assertIsArray($data['licenses']);
        $this->assertCount(10, $data['licenses']);

        $this->assertArrayHasKey('license', $data);
        $this->assertEquals(License::LICENSE_EDLIB, $data['license']);
    }
}
