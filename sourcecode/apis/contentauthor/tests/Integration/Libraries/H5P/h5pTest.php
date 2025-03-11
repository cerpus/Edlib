<?php

namespace Tests\Integration\Libraries\H5P;

use App\H5PContent;
use App\Libraries\H5P\h5p;
use Exception;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\Helpers\TestHelpers;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class h5pTest extends TestCase
{
    use RefreshDatabase;
    use TestHelpers;

    public const testContentDirectory = "content";
    public const testEditorDirectory = "editor";

    private $editorFilesDirectory;

    public function assertPreConditions(): void
    {
        $this->seed(TestH5PSeeder::class);
    }

    public function tearDown(): void
    {
        if (!is_null($this->editorFilesDirectory)) {
            $this->deleteEditorFilesDirectory();
        }
        parent::tearDown();
    }

    private function getTempDirectory()
    {
        return Storage::disk()->path('');
    }

    private function getEditorDirectory()
    {
        return Storage::disk()->path(self::testEditorDirectory);
    }

    private function getContentDirectory()
    {
        return Storage::disk()->path(self::testContentDirectory);
    }

    private function createUnitTestDirectories()
    {
        $tmpDir = $this->getTempDirectory();
        $editorDirectory = $this->getEditorDirectory();
        if (!is_dir($editorDirectory) && (mkdir($editorDirectory, 0777, true)) !== true) {
            throw new Exception("Can't create EditorFilesDirectory");
        }
        $this->editorFilesDirectory = realpath($tmpDir);
    }

    private function deleteEditorFilesDirectory()
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->editorFilesDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $pos = strpos($fileinfo->getRealPath(), realpath($this->getTempDirectory()), 0);
            if ($pos !== 0) {
                throw new Exception("Target is not in the tmp space");
            }
            if (!$action($fileinfo->getRealPath())) {
                throw new Exception("Could not delete the file/directory:" . $fileinfo->getRealPath());
            }
        }

        // TODO: figure out why "rmdir(/buckets/main_bucket): Resource busy" happens
        @rmdir($this->editorFilesDirectory);
    }

    private function createUploadedFiles($files)
    {
        foreach ($files as $filePath => $fileContent) {
            $fileDir = dirname($filePath);
            if (!is_dir($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            $pos = strpos($filePath, realpath($this->getTempDirectory()), 0);
            if ($pos !== 0) {
                throw new Exception("Target '$filePath' is not in the tmp space");
            }
            if (file_put_contents($filePath, $fileContent) === false) {
                throw new Exception("Could not write to file '$filePath'");
            }
        }
    }

    #[Test]
    public function createWithOutVersioningContent()
    {
        $request = Request::create('', parameters: [
            'library' => "H5P.Flashcards 1.1",
            'title' => "My Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}',
        ]);

        $this->createUnitTestDirectories();
        $this->createUploadedFiles(["{$this->getEditorDirectory()}/images/image-5805bff7c5330.jpg" => "Test image"]);

        $h5p = app(h5p::class);
        $content = $h5p->storeContent($request, null, "createContentUserId");
        $this->assertNotFalse($content);
        $this->assertEquals(1, $content['id']);
        $this->assertEquals("My Test Title", $content['title']);
        $this->assertEquals("createContentUserId", $content['user_id']);
        $this->assertFileExists("{$this->getContentDirectory()}/{$content['id']}/images/image-5805bff7c5330.jpg");
        $this->assertJson($content['params'], "Params not set correct");
        $contentParamsDecoded = json_decode($content['params']);
        $this->assertObjectHasProperty("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Hvor er ørreten?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, "license" => "U"]);

        $request = new Request();
        $request->replace([
            'library' => "H5P.Flashcards 1.1",
            'title' => "Updated Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Kan du se hvor ørreten er?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"BY","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}',
            'isDraft' => false,
        ]);

        $core = app(H5PCore::class);
        $storedContent = $core->loadContent($content['id']);
        $updatedContent = $h5p->storeContent($request, $storedContent, "createContentUserId");
        $this->assertNotFalse($updatedContent);
        $this->assertEquals(1, $updatedContent['id']);
        $this->assertEquals("Updated Test Title", $updatedContent['title']);
        $this->assertEquals("createContentUserId", $updatedContent['user_id']);
        $contentParamsDecoded = json_decode($updatedContent['params']);
        $this->assertObjectHasProperty("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Kan du se hvor ørreten er?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);
        $this->assertFileExists("{$this->getContentDirectory()}/{$updatedContent['id']}/images/image-5805bff7c5330.jpg");

        $h5pContent = H5PContent::find(1);
        $this->assertEquals($h5pContent->id, $updatedContent['id']);
        $this->assertEquals("Deltittel", $h5pContent->title);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, 'license' => "BY"]);
    }

    #[Test]
    public function createWithVersioningContent()
    {
        $request = Request::create('', parameters: [
            'library' => "H5P.Flashcards 1.1",
            'title' => "My Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}',
        ]);

        $this->createUnitTestDirectories();
        $this->createUploadedFiles(["{$this->getEditorDirectory()}/images/image-5805bff7c5330.jpg" => "Test image"]);

        $h5p = app(h5p::class);
        $content = $h5p->storeContent($request, null, "createContentUserId");
        $this->assertNotFalse($content);
        $this->assertEquals(1, $content['id']);
        $this->assertEquals("My Test Title", $content['title']);
        $this->assertEquals("createContentUserId", $content['user_id']);
        $this->assertFileExists("{$this->getContentDirectory()}/{$content['id']}/images/image-5805bff7c5330.jpg");
        $this->assertJson($content['params'], "Params not set correct");
        $contentParamsDecoded = json_decode($content['params']);
        $this->assertObjectHasProperty("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Hvor er ørreten?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, "license" => "U"]);

        $request = Request::create('', parameters: [
            'library' => "H5P.Flashcards 1.1",
            'title' => "Updated Test Title",
            'parameters' => '{"params": {"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Kan du se hvor ørreten er?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"BY","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}',
        ]);

        $core = app(H5PCore::class);
        $storedContent = $core->loadContent($content['id']);
        $storedContent['useVersioning'] = true;
        $updatedContent = $h5p->storeContent($request, $storedContent, "createContentUserId");
        $this->assertNotFalse($updatedContent);
        $this->assertEquals(2, $updatedContent['id']);
        $this->assertEquals("Updated Test Title", $updatedContent['title']);
        $this->assertEquals("createContentUserId", $updatedContent['user_id']);
        $contentParamsDecoded = json_decode($updatedContent['params']);
        $this->assertObjectHasProperty("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Kan du se hvor ørreten er?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);
        $this->assertFileExists("{$this->getContentDirectory()}/{$updatedContent['id']}/images/image-5805bff7c5330.jpg");

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents", ["id" => 2]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 3]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, 'license' => "U"]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 2, 'content_id' => 2, 'license' => "BY"]);
    }
}
