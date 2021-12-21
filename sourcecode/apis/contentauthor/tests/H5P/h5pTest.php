<?php

namespace Tests\H5P;

use App\H5PContent;
use Tests\TestCase;
use Tests\TestHelpers;
use App\Libraries\H5P\h5p;
use Tests\db\TestH5PSeeder;
use Illuminate\Http\Request;
use App\Libraries\H5P\H5Plugin;
use Tests\Traits\ContentAuthorStorageTrait;
use Tests\Traits\ResetH5PStatics;
use Illuminate\Foundation\Testing\RefreshDatabase;

class h5pTest extends TestCase
{
    use RefreshDatabase, TestHelpers, ResetH5PStatics, ContentAuthorStorageTrait;

    const testContentDirectory = "content";
    const testEditorDirectory = "editor";

    private $editorFilesDirectory;

    public function assertPreConditions(): void
    {
        $this->seed(TestH5PSeeder::class);
    }

    public function setUp(): void
    {
        parent::setUp();

        h5p::setUp();
        H5Plugin::setUp();

        $this->setUpContentAuthorStorage();
    }

    public function tearDown(): void
    {
        if (!is_null($this->editorFilesDirectory)) {
            $this->deleteEditorFilesDirectory();
        }
        parent::tearDown();
        h5p::setUp();
        H5Plugin::setUp();
    }

    private function getTempDirectory()
    {
        return $this->contentAuthorStorage->getBucketDisk()->path('');
    }

    private function getEditorDirectory()
    {
        return $this->contentAuthorStorage->getBucketDisk()->path(self::testEditorDirectory);
    }

    private function getContentDirectory()
    {
        return $this->contentAuthorStorage->getBucketDisk()->path(self::testContentDirectory);
    }

    private function createUnitTestDirectories()
    {
        $tmpDir = $this->getTempDirectory();
        $editorDirectory = $this->getEditorDirectory();
        if (!is_dir($editorDirectory) && (mkdir($editorDirectory, 0777, true)) !== true) {
            throw new \Exception("Can't create EditorFilesDirectory");
        }
        $this->editorFilesDirectory = realpath($tmpDir);
    }

    private function deleteEditorFilesDirectory()
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->editorFilesDirectory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $action = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $pos = strpos($fileinfo->getRealPath(), realpath($this->getTempDirectory()), 0);
            if ($pos !== 0) {
                throw new \Exception("Target is not in the tmp space");
            }
            if (!$action($fileinfo->getRealPath())) {
                throw new \Exception("Could not delete the file/directory:" . $fileinfo->getRealPath());
            }
        }
        if (!rmdir($this->editorFilesDirectory)) {
            throw new \Exception("Could not delete the editorFilesDirectory");
        }
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
                throw new \Exception("Target '$filePath' is not in the tmp space");
            }
            if (file_put_contents($filePath, $fileContent) === false) {
                throw new \Exception("Could not write to file '$filePath'");
            }
        }
    }


    /**
     * @test
     */
    public function createWithOutVersioningContent()
    {
        app()->instance('requestId', 123);

        $request = new Request([
            'library' => "H5P.Flashcards 1.1",
            'title' => "My Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}'
        ]);

        $this->createUnitTestDirectories();
        $this->createUploadedFiles([$this->getEditorDirectory() . DIRECTORY_SEPARATOR . "images/image-5805bff7c5330.jpg" => "Test image"]);

        $h5p = new h5p($this->getPDOConnection());
        $h5p->setEditorFilesDir($this->editorFilesDirectory);
        $h5p->setIsValidated(true);
        $h5p->setUserId("createContentUserId");
        $content = $h5p->storeContent($request);
        $this->assertNotFalse($content);
        $this->assertEquals(1, $content['id']);
        $this->assertEquals("My Test Title", $content['title']);
        $this->assertEquals("createContentUserId", $content['user_id']);
        $this->assertFileExists($this->getContentDirectory() . DIRECTORY_SEPARATOR . $content['id'] . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "image-5805bff7c5330.jpg");
        $this->assertJson($content['params'], "Params not set correct");
        $contentParamsDecoded = json_decode($content['params']);
        $this->assertObjectHasAttribute("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Hvor er ørreten?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, "license" => "U"]);

        $request = new Request([
            'library' => "H5P.Flashcards 1.1",
            'title' => "Updated Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Kan du se hvor ørreten er?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"BY","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}'
        ]);

        $core = $h5p->getH5pCore();
        $storedContent = $core->loadContent($content['id']);
        $updatedContent = $h5p->storeContent($request, $storedContent);
        $this->assertNotFalse($updatedContent);
        $this->assertEquals(1, $updatedContent['id']);
        $this->assertEquals("Updated Test Title", $updatedContent['title']);
        $this->assertEquals("createContentUserId", $updatedContent['user_id']);
        $contentParamsDecoded = json_decode($updatedContent['params']);
        $this->assertObjectHasAttribute("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Kan du se hvor ørreten er?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);
        $this->assertFileExists($this->getContentDirectory() . DIRECTORY_SEPARATOR . $updatedContent['id'] . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "image-5805bff7c5330.jpg");

        $h5pContent = H5PContent::find(1);
        $this->assertEquals($h5pContent->id, $updatedContent['id']);
        $this->assertEquals($h5pContent->title, "Deltittel");

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, 'license' => "BY"]);
    }

    /**
     * @test
     */
    public function createWithVersioningContent()
    {
        app()->instance('requestId', 123);

        $request = new Request([
            'library' => "H5P.Flashcards 1.1",
            'title' => "My Test Title",
            'parameters' => '{"params":{"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}'
        ]);

        $this->createUnitTestDirectories();
        $this->createUploadedFiles([$this->getEditorDirectory() . DIRECTORY_SEPARATOR . "images/image-5805bff7c5330.jpg" => "Test image"]);

        $h5p = new h5p($this->getPDOConnection());
        $h5p->setEditorFilesDir($this->editorFilesDirectory);
        $h5p->setIsValidated(true);
        $h5p->setUserId("createContentUserId");
        $content = $h5p->storeContent($request);
        $this->assertNotFalse($content);
        $this->assertEquals(1, $content['id']);
        $this->assertEquals("My Test Title", $content['title']);
        $this->assertEquals("createContentUserId", $content['user_id']);
        $this->assertFileExists($this->getContentDirectory() . DIRECTORY_SEPARATOR . $content['id'] . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "image-5805bff7c5330.jpg");
        $this->assertJson($content['params'], "Params not set correct");
        $contentParamsDecoded = json_decode($content['params']);
        $this->assertObjectHasAttribute("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Hvor er ørreten?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, "license" => "U"]);

        $request = new Request([
            'library' => "H5P.Flashcards 1.1",
            'title' => "Updated Test Title",
            'parameters' => '{"params": {"cards":[{"image":{"path":"images/image-5805bff7c5330.jpg","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Kan du se hvor ørreten er?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"license":"BY","authors":[],"changes":[],"extraTitle":"Deltittel","title":"Deltittel"}}'
        ]);

        $core = $h5p->getH5pCore();
        $storedContent = $core->loadContent($content['id']);
        $storedContent['useVersioning'] = true;
        $updatedContent = $h5p->storeContent($request, $storedContent);
        $this->assertNotFalse($updatedContent);
        $this->assertEquals(2, $updatedContent['id']);
        $this->assertEquals("Updated Test Title", $updatedContent['title']);
        $this->assertEquals("createContentUserId", $updatedContent['user_id']);
        $contentParamsDecoded = json_decode($updatedContent['params']);
        $this->assertObjectHasAttribute("cards", $contentParamsDecoded);
        $this->assertNotEmpty($contentParamsDecoded->cards);
        $this->assertEquals("Kan du se hvor ørreten er?", $contentParamsDecoded->cards[0]->text);
        $this->assertEquals("Her!", $contentParamsDecoded->cards[0]->answer);
        $this->assertFileExists($this->getContentDirectory() . DIRECTORY_SEPARATOR . $updatedContent['id'] . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "image-5805bff7c5330.jpg");

        $this->assertDatabaseHas("h5p_contents", ["id" => 1]);
        $this->assertDatabaseHas("h5p_contents", ["id" => 2]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 3]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 1, 'content_id' => 1, 'license' => "U"]);
        $this->assertDatabaseHas("h5p_contents_metadata", ["id" => 2, 'content_id' => 2, 'license' => "BY"]);
    }

    /**
     * @test
     */
    public function contentNotValidated()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Content must be validated before storing");

        $request = new Request([
            'library' => "H5P.Flashcards 1.1",
            'title' => "My Test Title",
            'parameters' => '{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true}'
        ]);

        $h5p = new h5p($this->getPDOConnection());
        $h5p->storeContent($request);
    }

    /**
     * @test
     */
    public function invalidFields()
    {
        $h5p = new h5p($this->getPDOConnection());
        $h5pContent = new H5PContent();
        $request = new Request(['title' => null]);
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The title field is required.", $h5p->getErrorMessage());

        $request->query->set('title', 'TestTitle');
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The library field is required when libraryid is not present.", $h5p->getErrorMessage());

        $request->query->set('library', null);
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The library field is required when libraryid is not present.", $h5p->getErrorMessage());

        $request->query->set('libraryid',100000);
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The selected libraryid is invalid.", $h5p->getErrorMessage());

        $request->query->remove('libraryid');
        $request->merge([
            "library" => "H5P.TestLibrary",
            "parameters" => null
        ]);
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The parameters field is required.", $h5p->getErrorMessage());

        $request->query->set('parameters',"NotJson");
        $invalid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertFalse($invalid);
        $this->assertEquals("The parameters must be a valid JSON string.", $h5p->getErrorMessage());

        $h5p = new h5p($this->getPDOConnection());
        $request->query->set('parameters', json_encode(["key" => "value"]));
        $valid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertTrue($valid);
        $this->assertNull($h5p->getErrorMessage());

        $request->query->set('libraryid', 1);
        $valid = $h5p->validateStoreInput($request, $h5pContent);
        $this->assertTrue($valid);
        $this->assertNull($h5p->getErrorMessage());
    }
}
