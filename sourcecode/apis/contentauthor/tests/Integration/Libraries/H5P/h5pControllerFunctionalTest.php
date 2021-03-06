<?php

namespace Tests\Integration\Libraries\H5P;

use App\Events\ContentCreated;
use App\Events\ContentCreating;
use App\Events\ContentDeleted;
use App\Events\ContentDeleting;
use App\Events\ContentUpdated;
use App\Events\ContentUpdating;
use App\H5PContent;
use App\H5pLti;
use App\Http\Controllers\H5PController;
use App\Http\Requests\H5PStorageRequest;
use App\Libraries\H5P\h5p;
use App\Libraries\H5P\H5PLibraryAdmin;
use Cerpus\VersionClient\VersionClient;
use Cerpus\VersionClient\VersionData;
use Exception;
use H5PCore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use stdClass;
use Tests\Helpers\MockMQ;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;


class h5pControllerFunctionalTest extends TestCase
{
    use RefreshDatabase, MockMQ;

    protected array $fakedEvents = [
        ContentCreating::class,
        ContentCreated::class,
        ContentUpdating::class,
        ContentUpdated::class,
        ContentDeleting::class,
        ContentDeleted::class,
    ];

    public function assertPreConditions(): void
    {
        $this->seed(TestH5PSeeder::class);
    }

    /**
     * @test
     *
     */
    public function addAuthorToParameters1()
    {
        $this->withSession(["name" => "user"]);
        $params = H5PController::addAuthorToParameters('{"params": {}}');

        $this->assertEquals('{"params":{},"metadata":{"authors":[{"name":"user","role":"Author"}]}}', $params);
    }

    /**
     * @test
     *
     */
    public function addAuthorToParameters2()
    {
        $this->withSession(["name" => "user"]);
        $params = H5PController::addAuthorToParameters('{"params":{},"metadata":{"authors":[{"name":"user 2","role":"Author"}]}}');

        $this->assertEquals('{"params":{},"metadata":{"authors":[{"name":"user 2","role":"Author"}]}}', $params);
    }

    /**
     * @test
     *
     */
    public function addAuthorToParameters3()
    {
        $this->withSession(["name" => ""]);
        $params = H5PController::addAuthorToParameters('{"params":[]}');

        $this->assertEquals('{"params":[]}', $params);
    }

    /**
     * @test
     *
     */
    public function storeContent()
    {
        Event::fake($this->fakedEvents);

        $request = new H5PStorageRequest([], [
            'title' => "H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ??rreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "H5P Title"}}',
            "license" => 'BY-NC',
        ]);

        app()->instance('requestId', Uuid::uuid4()->toString());

        $versionClient = $this->getMockBuilder(VersionClient::class)
            ->onlyMethods(["createVersion"])
            ->getMock();

        $versionClient->method("createVersion")
            ->willReturnCallback(function () {
                $responseData = new stdClass();
                $responseData->id = "abcdefghijklmnopqrstuvwxyz";

                $versionData = new VersionData();
                $versionData->populate($responseData);
                return $versionData;
            });
        app()->instance(VersionClient::class, $versionClient);

        $this->withSession(["authId" => "user_1"]);

        /** @var H5pLti $h5pLti */
        $h5pLti = $this->getMockBuilder(H5pLti::class)->getMock();
        $h5pController = new H5PController($h5pLti, app(h5p::class), app(H5PLibraryAdmin::class));

        $response = $h5pController->store($request);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $responseData = json_decode($response->getContent());
        $this->assertObjectHasAttribute('url', $responseData);
        $this->assertEquals("http://localhost/h5p/1", $responseData->url);

        $this->assertDatabaseHas("h5p_contents", ["id" => 1, 'license' => 'BY-NC']);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);

        $h5pContent = H5PContent::find(1);
        $this->assertEquals("abcdefghijklmnopqrstuvwxyz", $h5pContent->version_id);
    }

    /**
     * @test
     * @throws Exception
     */
    public function updateContent()
    {
        Event::fake($this->fakedEvents);
        $core = resolve(H5PCore::class);

        $request = new H5PStorageRequest();
        $request->replace([
            'title' => "H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ??rreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "H5P Title"}}',
            "license" => 'BY-NC-ND',
            "isDraft" => false
        ]);

        app()->instance('requestId', Uuid::uuid4()->toString());

        $versionClient = $this->getMockBuilder(VersionClient::class)
            ->onlyMethods(["createVersion"])
            ->getMock();

        $responseA = new stdClass();
        $responseA->id = "AAAAAAAAAA";
        $versionA = new VersionData();
        $versionA->populate($responseA);

        $responseB = new stdClass();
        $responseB->id = "BBBBBBBBBB";
        $versionB = new VersionData();
        $versionB->populate($responseB);

        $versionClient
            ->expects($this->exactly(2))
            ->method("createVersion")
            ->willReturnOnConsecutiveCalls($versionA, $versionB);

        app()->instance(VersionClient::class, $versionClient);

        $this->withSession(["authId" => "user_1"]);

        /** @var H5pLti $h5pLti */
        $h5pLti = $this->getMockBuilder(H5pLti::class)->getMock();
        $h5pController = new H5PController($h5pLti, app(h5p::class), app(H5PLibraryAdmin::class));

        $response = $h5pController->store($request);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $this->assertDatabaseHas("h5p_contents", ["id" => 1, "license" => 'BY-NC-ND']);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 2]);

        $h5pContent = H5PContent::find(1);
        $this->assertEquals("AAAAAAAAAA", $h5pContent->version_id);

        $request = new H5PStorageRequest();
        $request->replace([
            'title' => "Updated H5P Title",
            "library" => "H5P.Flashcards 1.1",
            "parameters" => '{"params":{"cards":[{"image":{"path":"","mime":"image/jpeg","copyright":{"license":"U"},"width":3840,"height":2160},"text":"Hvor er ??rreten?","answer":"Her!","tip":""}],"progressText":"Card @card of @total","next":"Next","previous":"Previous","checkAnswerText":"Check","showSolutionsRequiresInput":true},"metadata":{"title": "Updated H5P Title"}}',
            "isDraft" => false
        ]);

        $response = $h5pController->update($request, $h5pContent, $core);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent());
        $this->assertObjectHasAttribute('url', $responseData);
        $this->assertEquals("http://localhost/h5p/2", $responseData->url);

        $this->assertDatabaseHas("h5p_contents", ["id" => 2]);
        $this->assertDatabaseMissing("h5p_contents", ["id" => 3]);

        $updatedH5pContent = H5PContent::find(2);
        $this->assertEquals("BBBBBBBBBB", $updatedH5pContent->version_id);
        $this->assertEquals("Updated H5P Title", $updatedH5pContent->title);
    }
}
