<?php

namespace Tests\Integration\Http\Controllers;

use App\ApiModels\User;
use App\Http\Libraries\License;
use App\Link;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\View\View;
use Tests\Helpers\MockAuthApi;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;
    use MockAuthApi;
    use WithFaker;

    public function test_doShow(): void
    {
        $user = new User($this->faker->uuid, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->session([
            'authId' => $user->getId(),
        ]);
        $link = Link::factory()->create([
            'license' => License::LICENSE_BY_NC_ND,
            'owner_id' => $user->getId(),
        ]);

        $request = new Oauth1Request('POST', url('/link/' . $link->getId()), [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'ext_user_id' => "1",
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'launch_presentation_locale' => "nb",
            'launch_presentation_css_url' => 'my-styles.css',
        ]);
        $request = $this->app->make(SignerInterface::class)->sign(
            $request,
            $this->app->make(CredentialStoreInterface::class),
        );

        $result = $this->post('link/' . $link->getId(), $request->toArray())
        ->assertOk();

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result->getOriginalContent());
        $data = $result->getOriginalContent()->getData();
        $this->assertArrayHasKey('styles', $data);
        $this->assertContains('my-styles.css', $data['styles']);
    }
}
