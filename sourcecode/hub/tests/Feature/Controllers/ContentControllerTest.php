<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Enums\ContentUserRole;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\LtiPlatform;
use App\Models\LtiTool;
use App\Models\User;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\ContentItemsSerializer;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Cerpus\EdlibResourceKit\Oauth1\Request;
use Cerpus\EdlibResourceKit\Oauth1\SignerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLtiUpdate(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $oauthSigner = $this->app->make(SignerInterface::class);
        $serializer = new ContentItemsSerializer();

        $content = Content::factory()
            ->withUser($owner)
            ->shared()
            ->create();
        $firstVersion = ContentVersion::factory()
            ->published()
            ->create([
                'content_id' => $content->id,
                'title' => 'First version',
            ]);
        $tool = $firstVersion->tool;
        $this->assertInstanceOf(LtiTool::class, $tool);
        $toBeCopiedVersion = ContentVersion::factory()
            ->published()
            ->create([
                'content_id' => $content->id,
                'title' => 'The source version',
                'lti_tool_id' => $tool->id,
                'previous_version_id' => $firstVersion->id,
            ]);
        ContentVersion::factory()
            ->published()
            ->create([
                'content_id' => $content->id,
                'title' => 'Latest version',
                'lti_tool_id' => $tool->id,
                'previous_version_id' => $toBeCopiedVersion->id,
            ]);

        // The platform the edit request came from
        $platform = LtiPlatform::factory()->create(['authorizes_edit' => true]);
        Session::put('lti.oauth_consumer_key', $platform->key);
        // The platform requested a copy to be made before saving the changes
        Session::put('lti.ext_edlib3_copy_before_save', '1');
        Session::put('intent-to-edit.' . $content->id, true);

        // After user is done editing and saves, the Tool sends a request to E3 to inform that the content was updated,
        // this is where the content is copied
        $url = route('content.lti-update', [$tool, $content, $toBeCopiedVersion]);
        $contentItem = new LtiLinkItem(
            title: 'Edit on copy',
            url: 'http://example.com/r/42',
        );

        $request = $oauthSigner->sign(
            new Request('POST', $url, [
                'lti_version' => 'LTI-1p0',
                'lti_message_type' => 'ContentItemSelection',
                'content_item_return_url' => 'http://example.com/',
                'lis_person_contact_email_primary' => $owner->email,
                'content_items' => json_encode($serializer->serialize([$contentItem]), flags: JSON_THROW_ON_ERROR),
            ]),
            $tool->getOauth1Credentials(),
        );

        $response = $this->actingAs($user)
            ->post($url, $request->toArray())
            ->assertOk()
            ->original;

        $this->assertInstanceOf(View::class, $response);
        $data = $response->getData();

        $parsedUrl = parse_url($data['url'], PHP_URL_PATH);
        $this->assertNotEmpty($parsedUrl);
        $path = explode('/', $parsedUrl);
        $contentCopyId = $path[2]; // Id of the copied content
        $versionUpdateId = $path[4]; // Id of the edited version on the copied content

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
        ]);
        $this->assertDatabaseHas('content_user', [
            'content_id' => $content->id,
            'user_id' => $owner->id,
            'role' => ContentUserRole::Owner,
        ]);

        $this->assertDatabaseHas('contents', [
            'id' => $contentCopyId,
        ]);
        $this->assertDatabaseHas('content_user', [
            'content_id' => $contentCopyId,
            'user_id' => $user->id,
            'role' => ContentUserRole::Owner,
        ]);

        // The copied version and the edited version should be the only two on the content copy
        $versionsOnCopy = ContentVersion::where('content_id', $contentCopyId)->get();
        $this->assertCount(2, $versionsOnCopy);

        // The requested version is copied
        $copiedVersion = ContentVersion::where('content_id', '=', $contentCopyId)->where('previous_version_id', '=', $toBeCopiedVersion->id)->first();
        $this->assertNotNull($copiedVersion);
        $this->assertNotEmpty($toBeCopiedVersion->title);
        $this->assertStringStartsWith($toBeCopiedVersion->title, $copiedVersion->title);
        $this->assertSame($copiedVersion->lti_launch_url, $toBeCopiedVersion->lti_launch_url);

        // ... and the edited version is next version from that
        $updatedVersion = ContentVersion::where('content_id', '=', $contentCopyId)->where('previous_version_id', '=', $copiedVersion->id)->first();
        $this->assertNotNull($updatedVersion);
        $this->assertSame($versionUpdateId, $updatedVersion->id);
        $this->assertSame($contentItem->getTitle(), $updatedVersion->title);
        $this->assertSame($contentItem->getUrl(), $updatedVersion->lti_launch_url);
    }
}
