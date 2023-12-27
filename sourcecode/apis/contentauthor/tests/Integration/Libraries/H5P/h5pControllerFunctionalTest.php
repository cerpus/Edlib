<?php

namespace Tests\Integration\Libraries\H5P;

use App\ContentVersions;
use App\H5PContent;
use App\Http\Controllers\H5PController;
use App\Http\Requests\H5PStorageRequest;
use Exception;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\Helpers\MockMQ;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class h5pControllerFunctionalTest extends TestCase
{
    use RefreshDatabase;
    use MockMQ;

    public function assertPreConditions(): void
    {
        $this->seed(TestH5PSeeder::class);
    }

    /**
     * @test
     */
    public function addAuthorToParameters1()
    {
        $this->withSession(["name" => "user"]);
        $params = H5PController::addAuthorToParameters('{"params": {}}');

        $this->assertEquals('{"params":{},"metadata":{"authors":[{"name":"user","role":"Author"}]}}', $params);
    }

    /**
     * @test
     */
    public function addAuthorToParameters2()
    {
        $this->withSession(["name" => "user"]);
        $params = H5PController::addAuthorToParameters('{"params":{},"metadata":{"authors":[{"name":"user 2","role":"Author"}]}}');

        $this->assertEquals('{"params":{},"metadata":{"authors":[{"name":"user 2","role":"Author"}]}}', $params);
    }

    /**
     * @test
     */
    public function addAuthorToParameters3()
    {
        $this->withSession(["name" => ""]);
        $params = H5PController::addAuthorToParameters('{"params":[]}');

        $this->assertEquals('{"params":[]}', $params);
    }

    /**
     * @test
     */
    public function storeContent()
    {
        $request = new H5PStorageRequest([], [
            'title' => "H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "H5P Title"}}',
            "license" => 'BY-NC',
        ]);

        $this->withSession(["authId" => "user_1"]);

        $h5pController = app(H5PController::class);
        $response = $h5pController->store($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $responseData = json_decode($response->getContent());
        $this->assertObjectHasAttribute('url', $responseData);
        $this->assertEquals("http://localhost/h5p/1/edit", $responseData->url);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1, 'license' => 'BY-NC']);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);

        $h5pContent = H5PContent::find(1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5pContent->version_id,
            'content_id' => $h5pContent->id,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);
    }

    /**
     * @test
     * @throws Exception
     */
    public function updateContent()
    {
        $core = resolve(H5PCore::class);

        $request = new H5PStorageRequest();
        $request->replace([
            'title' => "H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "H5P Title"}}',
            "license" => 'BY-NC-ND',
            "isDraft" => false
        ]);

        $this->withSession(["authId" => "user_1"]);

        $h5pController = app(H5PController::class);
        $response = $h5pController->store($request);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $this->assertDatabaseHas("h5p_contents", ["id" => 1, "license" => 'BY-NC-ND']);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);

        $h5pContent = H5PContent::find(1);
        $this->assertDatabaseHas('content_versions', [
            'id' => $h5pContent->version_id,
            'content_id' => $h5pContent->id,
            'version_purpose' => ContentVersions::PURPOSE_CREATE,
        ]);

        $request = new H5PStorageRequest();
        $request->replace([
            'title' => "Updated H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ørreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "Updated H5P Title"}}',
            "isDraft" => false,
        ]);

        $response = $h5pController->update($request, $h5pContent, $core);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent());
        $this->assertObjectHasAttribute('url', $responseData);
        $this->assertEquals("http://localhost/h5p/2/edit", $responseData->url);

        $this->assertDatabaseHas("h5p_contents", ["id" => 2]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 3]);

        $updatedH5pContent = H5PContent::find(2);
        $this->assertDatabaseHas('content_versions', [
            'id' => $updatedH5pContent->version_id,
            'content_id' => $updatedH5pContent->id,
            'version_purpose' => ContentVersions::PURPOSE_UPDATE,
        ]);
        $this->assertEquals("Updated H5P Title", $updatedH5pContent->title);
    }
}
