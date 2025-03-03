<?php

namespace Tests\Integration\Libraries\H5P;

use App\Content;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\User;
use Exception;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\MockH5PAdapterInterface;
use Tests\Helpers\TestHelpers;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class CRUTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;
    use WithFaker;
    use MockH5PAdapterInterface;

    public const testDirectory = "h5pstorage";
    public const testContentDirectory = "content";
    public const testEditorDirectory = "editor";

    #[Test]
    public function test_environment()
    {
        $this->assertEquals('/tmp', env('TEST_FS_ROOT'));

        $dest = env('TEST_FS_ROOT') . '/tree.jpg';
        $this->assertTrue(copy(__DIR__ . '/../../../files/tree.jpg', $dest), "Unable to copy file to $dest");

        $this->assertFileExists($dest, "File $dest does not exist");
        $this->assertTrue(unlink($dest), "Unable to remove file $dest");
        $this->assertFileDoesNotExist($dest, "File $dest still exist");
    }

    #[DataProvider('provider_create_and_update_h5p_using_web_request')]
    #[Test]
    public function create_and_update_h5p_using_web_request(bool $useLinearVersioning)
    {
        Config::set('feature.linear-versioning', $useLinearVersioning);

        $owner = User::factory()->make();
        $collaborator = User::factory()->make(['email' => 'a@b.com']);
        $copyist = User::factory()->make();

        $this->setUpH5PLibrary();
        $this->createUnitTestDirectories();
        $this->setupH5PAdapter([
            'getAdapterName' => "UnitTest",
        ]);

        $this->assertCount(0, H5PContent::all());
        $this->withSession([
            'authId' => $owner->auth_id,
            'name' => $owner->name,
            'email' => $owner->email,
            'verifiedEmails' => [$owner->email],
        ])
            ->post(route('h5p.store'), [
                '_token' => csrf_token(),
                'title' => 'Tittel',
                'action' => 'create',
                'library' => 'H5P.Dialogcards 1.5',
                'parameters' => '{"params":{"description":"Do it now!\n","dialogs":[{"tips":{},"text":"<p>Question</p>\n","answer":"<p>Answer</p>\n"}],"behaviour":{"enableRetry":true,"disableBackwardsNavigation":false,"scaleTextNotCard":false,"randomCards":false},"answer":"Turn","next":"Next","prev":"Previous","retry":"Retry","progressText":"Card @card of @total","title":"<p>Ny tittel</p>\n"},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Tittel","title":"Tittel"}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com',
                'license' => "PRIVATE",
                'isDraft' => 0,
                'maxScore' => 3,
            ])
            ->assertStatus(Response::HTTP_CREATED); // Redirects after save

        $this->assertDatabaseCount('h5p_contents', 1);
        $h5p = H5PContent::find(1);
        $this->assertCount(1, $h5p->collaborators);
        $this->assertDatabaseHas('h5p_contents', ['id' => 1, 'title' => 'Tittel']);
        $firstVersion = $h5p->version_id;
        $this->assertDatabaseHas('content_versions', [
            'id' => $firstVersion,
            'content_id' => 1,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_CREATE,
        ]);

        $this->withSession([
            'authId' => $owner->auth_id,
            'name' => $owner->name,
            'email' => $owner->email,
            'verifiedEmails' => [$owner->email],
        ])
            ->put(route('h5p.update', 1), [
                '_token' => csrf_token(),
                'title' => 'Tittel',
                'action' => 'create',
                'library' => 'H5P.Dialogcards 1.5',
                'parameters' => '{"params":{"description":"Do it now!\n","dialogs":[{"tips":{},"text":"<p>Question</p>\n","answer":"<p>Answer</p>\n"}],"behaviour":{"enableRetry":true,"disableBackwardsNavigation":false,"scaleTextNotCard":false,"randomCards":false},"answer":"Turn","next":"Next","prev":"Previous","retry":"Retry","progressText":"Card @card of @total","title":"<p>Ny tittel</p>\n"},"metadata":{"license":"U","authors":[{"name": "' . $owner->name . '","role":"Author"}],"changes":[],"extraTitle":"Tittel","title":"Tittel"}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com,d@e.com',
                'license' => "PRIVATE",
                'maxScore' => 2,
                'isDraft' => 0,
            ]);

        $h5p->refresh();
        $this->assertDatabaseCount('h5p_contents', 1);
        $this->assertCount(2, $h5p->collaborators);
        $this->assertDatabaseHas('h5p_contents', ['id' => 1, 'title' => 'Tittel']);

        $this->assertDatabaseCount('content_versions', 2);
        $secondVersion = $h5p->version_id;
        $this->assertDatabaseHas('content_versions', [
            'id' => $secondVersion,
            'parent_id' => $firstVersion,
            'content_id' => 1,
            'content_type' => Content::TYPE_H5P,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $this->withSession([
            'authId' => $owner->auth_id,
            'name' => $owner->name,
            'email' => $owner->email,
            'verifiedEmails' => [$owner->email],
        ])
            ->put(route('h5p.update', 1), [
                '_token' => csrf_token(),
                'title' => 'Tittel 2', // Will trigger a new version
                'action' => 'create',
                'library' => 'H5P.Dialogcards 1.5',
                'parameters' => '{"params":{"description":"Do it now!\n","dialogs":[{"tips":{},"text":"<p>Question</p>\n","answer":"<p>Answer</p>\n"}],"behaviour":{"enableRetry":true,"disableBackwardsNavigation":false,"scaleTextNotCard":false,"randomCards":false},"answer":"Turn","next":"Next","prev":"Previous","retry":"Retry","progressText":"Card @card of @total","title":"<p>Ny tittel</p>\n"},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Tittel 2","title":"Tittel 2"}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com,d@e.com,f@g.com',
                'license' => "PRIVATE",
                'isDraft' => 0,
            ]);

        $this->assertDatabaseCount('h5p_contents', 2);
        $this->assertCount(2, H5PContent::find(1)->collaborators); // Original still has two collaborators
        $this->assertCount(3, H5PContent::find(2)->collaborators); // New has three collaborators
        $this->assertDatabaseHas('h5p_contents', ['id' => 1, 'title' => 'Tittel']);
        $this->assertDatabaseHas('h5p_contents', ['id' => 2, 'title' => 'Tittel 2']);

        $this->assertDatabaseCount('content_versions', 3);
        $thirdVersion = H5PContent::find(2)->version_id;
        $this->assertDatabaseHas('content_versions', [
            'id' => $thirdVersion,
            'content_id' => 2,
            'parent_id' => $secondVersion,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $this->withSession([
            'authId' => $collaborator->auth_id,
            'email' => $collaborator->email,
            'name' => $collaborator->name,
            'verifiedEmails' => [$collaborator->email],
        ])
            ->put(route('h5p.update', $h5p->id), [
                '_token' => csrf_token(),
                'title' => 'Tittel 3', // Will trigger a new version
                'action' => 'create',
                'library' => 'H5P.Dialogcards 1.5',
                'parameters' => '{"params":{"description":"Do it now!\n","dialogs":[{"tips":{},"text":"<p>Question</p>\n","answer":"<p>Answer</p>\n"}],"behaviour":{"enableRetry":true,"disableBackwardsNavigation":false,"scaleTextNotCard":false,"randomCards":false},"answer":"Turn","next":"Next","prev":"Previous","retry":"Retry","progressText":"Card @card of @total","title":"<p>Ny tittel</p>\n"},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Tittel 3","title":"Tittel 3"}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com,d@e.com,f@g.com',
                'license' => "PRIVATE",
                'isDraft' => 0,
            ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('h5p_contents', 3);
        $this->assertCount(2, H5PContent::find(3)->collaborators); // Collaborators not updated
        $this->assertDatabaseHas('h5p_contents', ['user_id' => $owner->auth_id, 'title' => 'Tittel 3']); // Owner has not changed, title updated

        $this->assertDatabaseCount('content_versions', 4);
        $fourthVersion = H5PContent::find(3)->version_id;
        $this->assertDatabaseHas('content_versions', [
            'id' => $fourthVersion,
            'content_id' => 3,
            'parent_id' => $useLinearVersioning ? $thirdVersion : $secondVersion,
            'version_purpose' => ContentVersion::PURPOSE_UPDATE,
        ]);

        $h5p->license = 'BY';
        $h5p->save();

        $this->withSession([
            'authId' => $copyist->auth_id,
            'email' => $copyist->email,
            'name' => $copyist->name,
            'verifiedEmails' => [$copyist->email],
        ])
            ->put(route('h5p.update', $h5p->id), [
                '_token' => csrf_token(),
                'title' => 'Tittel 4', // Will trigger a new version
                'action' => 'create',
                'library' => 'H5P.Dialogcards 1.5',
                'parameters' => '{"params":{"description":"Do it now!\n","dialogs":[{"tips":{},"text":"<p>Question</p>\n","answer":"<p>Answer</p>\n"}],"behaviour":{"enableRetry":true,"disableBackwardsNavigation":false,"scaleTextNotCard":false,"randomCards":false},"answer":"Turn","next":"Next","prev":"Previous","retry":"Retry","progressText":"Card @card of @total","title":"<p>Ny tittel</p>\n"},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Tittel 4","title":"Tittel 4"}}',
                'frame' => "1",
                'copyright' => "1",
                'isDraft' => 0,
                //'col_email' => '',
                //'col-emails' => 'a@b.com,d@e.com,f@g.com',
                //'license' => "PRIVATE",
            ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseCount('h5p_contents', 4); // New H5P in db
        $this->assertDatabaseHas('h5p_contents', ['user_id' => $copyist->auth_id, 'title' => 'Tittel 4']); // Owner and title updated
        $this->assertCount(0, H5PContent::find(4)->collaborators); //No collaborators on new resource

        $this->assertDatabaseCount('content_versions', 5);
        $this->assertDatabaseHas('content_versions', [
            'id' => H5PContent::find(4)->version_id,
            'content_id' => 4,
            'parent_id' => $useLinearVersioning ? $fourthVersion : $secondVersion,
            'version_purpose' => ContentVersion::PURPOSE_COPY,
        ]);
    }

    public static function provider_create_and_update_h5p_using_web_request(): Generator
    {
        yield 'linear_versioning' => [true];
        yield 'non-linear_versioning' => [false];
    }

    private function setUpH5PLibrary(): void
    {
        H5PLibrary::factory()->create([
            'name' => 'H5P.Dialogcards',
            'title' => 'Dialog Cards',
            'major_version' => 1,
            'minor_version' => 5,
            'patch_version' => 0,
            'restricted' => 0,
            'fullscreen' => false,
            'embed_types' => '',
            'preloaded_js' => 'js/dialogcards.js',
            'preloaded_css' => 'css/dialogcards.css',
            'drop_library_css' => '',
            'semantics' => '[ { "name": "title", "type": "text", "widget": "html", "label": "Title", "importance": "high", "optional": true, "tags": [ "p", "br", "strong", "em" ] }, { "name": "description", "type": "text", "widget": "html", "label": "Task description", "importance": "medium", "default": "", "optional": true, "tags": [ "p", "br", "strong", "em" ] }, { "name": "dialogs", "type": "list", "importance": "high", "widgets": [ { "name": "VerticalTabs", "label": "Default" } ], "label": "Dialogs", "entity": "dialog", "min": 1, "defaultNum": 1, "field": { "name": "question", "type": "group", "label": "Question", "importance": "high", "fields": [ { "name": "text", "type": "text", "widget": "html", "tags": [ "p", "br", "strong", "em" ], "label": "Text", "importance": "high", "description": "Hint for the first part of the dialogue" }, { "name": "answer", "type": "text", "widget": "html", "tags": [ "p", "br", "strong", "em" ], "label": "Answer", "importance": "high", "description": "Hint for the second part of the dialogue" }, { "name": "image", "type": "image", "label": "Image", "importance": "high", "optional": true, "description": "Optional image for the card. (The card may use just an image, just a text or both)" }, { "name": "audio", "type": "audio", "label": "Audio files", "importance": "low", "optional": true }, { "name": "tips", "type": "group", "label": "Tips", "importance": "low", "fields": [ { "name": "front", "type": "text", "label": "Tip for text", "importance": "low", "optional": true, "description": "Tip for the first part of the dialogue" }, { "name": "back", "type": "text", "label": "Tip for answer", "importance": "low", "optional": true, "description": "Tip for the second part of the dialogue" } ] } ] } }, { "name": "behaviour", "type": "group", "label": "Behavioural settings.", "importance": "low", "description": "These options will let you control how the task behaves.", "optional": true, "fields": [ { "name": "enableRetry", "type": "boolean", "label": "Enable \\"Retry\\" button", "importance": "low", "default": true, "optional": true }, { "name": "disableBackwardsNavigation", "type": "boolean", "label": "Disable backwards navigation", "importance": "low", "description": "This option will only allow you to move forward with Dialog Cards", "optional": true, "default": false }, { "name": "scaleTextNotCard", "type": "boolean", "label": "Scale the text to fit inside the card", "importance": "low", "description": "Unchecking this option will make the card adapt its size to the size of the text", "default": false } ] }, { "label": "Text for the turn button", "importance": "low", "name": "answer", "type": "text", "default": "Turn", "common": true }, { "label": "Text for the next button", "importance": "low", "type": "text", "name": "next", "default": "Next", "common": true }, { "name": "prev", "type": "text", "label": "Text for the previous button", "importance": "low", "default": "Previous", "common": true }, { "name": "retry", "type": "text", "label": "Text for the retry button", "importance": "low", "default": "Retry", "common": true }, { "name": "progressText", "type": "text", "label": "Progress text", "importance": "low", "description": "Available variables are @card and @total.", "default": "Card @card of @total", "common": true } ]',
            'tutorial_url' => '',
        ]);
    }

    /**
     * @throws Exception
     */
    private function createUnitTestDirectories(): void
    {
        $editorDirectory = $this->getEditorDirectory();
        if (!is_dir($editorDirectory) && (mkdir($editorDirectory, 0777, true)) !== true) {
            throw new Exception("Can't create EditorFilesDirectory");
        }
    }

    private function getTempDirectory(): string
    {
        return sys_get_temp_dir() . '/' . self::testDirectory;
    }

    private function getEditorDirectory(): string
    {
        return $this->getTempDirectory() . '/' . self::testEditorDirectory;
    }

    #[Test]
    public function upgradeContentNoExtraChanges_validParams_thenSuccess()
    {
        Event::fake();

        $this->seed(TestH5PSeeder::class);
        $owner = User::factory()->make();
        $content = H5PContent::factory()->create([
            'user_id' => $owner->auth_id,
            'parameters' => '{"simpleTest":"SimpleTest","original":true}',
            'library_id' => 39,
        ]);

        $this->assertCount(1, H5PContent::all());
        $this->withSession([
            'authId' => $owner->auth_id,
            'name' => $owner->name,
            'email' => $owner->email,
            'verifiedEmails' => [$owner->email],
        ])
            ->put(route('h5p.update', $content->id), [
                '_token' => csrf_token(),
                'title' => $content->title,
                'action' => 'create',
                'library' => 'H5P.MarkTheWords 1.6',
                'parameters' => '{"params":{"simpleTest":"SimpleTest","original":false,"upgraded":"Yess"},"metadata":{}}',
                'upgradeParams' => '{"params":{"simpleTest":"SimpleTest","original":true},"metadata":{}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com',
                'license' => "PRIVATE",
                'isDraft' => 0,
            ])
            ->assertStatus(Response::HTTP_OK); // Redirects after save

        $all = H5PContent::all();
        $this->assertCount(2, $all);
        $this->assertEquals(39, $all->first()->library_id);
        $this->assertEquals(90, $all->last()->library_id);
        Event::assertDispatched(H5PWasSaved::class);
    }

    #[Test]
    public function upgradeContentExtraChanges_validParams_thenSuccess()
    {
        Event::fake();

        $this->seed(TestH5PSeeder::class);
        $owner = User::factory()->make();
        $content = H5PContent::factory()->create([
            'user_id' => $owner->auth_id,
            'parameters' => '{"simpleTest":"SimpleTest","original":true}',
            'library_id' => 39,
        ]);

        $this->createUnitTestDirectories();

        $this->assertCount(1, H5PContent::all());
        $this->withSession([
            'authId' => $owner->auth_id,
            'name' => $owner->name,
            'email' => $owner->email,
            'verifiedEmails' => [$owner->email],
        ])
            ->put(route('h5p.update', $content->id), [
                '_token' => csrf_token(),
                'title' => 'Title',
                'action' => 'create',
                'library' => 'H5P.MarkTheWords 1.6',
                'parameters' => '{"params":{"simpleTest":"SimpleTest","original":false,"upgraded":"Hell yess!"},"metadata":{}}',
                'upgradeParams' => '{"params":{"simpleTest":"SimpleTest","original":false,"upgraded":"Yess"},"metadata":{}}',
                'frame' => "1",
                'copyright' => "1",
                'col_email' => '',
                'col-emails' => 'a@b.com',
                'license' => "PRIVATE",
                'isDraft' => 0,
            ])
            ->assertStatus(Response::HTTP_OK); // Redirects after save
        $all = H5PContent::all();
        $this->assertCount(2, $all);
        $first = $all->first();
        $second = $all->get(1);
        $this->assertEquals($content->title, $first->title);
        $this->assertEquals(39, $first->library_id);
        $this->assertJsonStringEqualsJsonString('{"simpleTest":"SimpleTest","original":true}', $first->parameters);
        $this->assertEquals("Title", $second->title);
        $this->assertEquals(90, $second->library_id);
        $this->assertJsonStringEqualsJsonString('{"simpleTest":"SimpleTest","original":false,"upgraded":"Hell yess!"}', $second->parameters);
        Event::assertDispatched(H5PWasSaved::class);
    }
}
