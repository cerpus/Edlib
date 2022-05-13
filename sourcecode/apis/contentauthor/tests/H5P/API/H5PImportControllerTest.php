<?php

namespace App\Libraries\H5P {

    use Illuminate\Support\Str;

    function is_uploaded_file($filename)
    {
        return collect([
            '/tests/files/sample-blanks-1.6.h5p',
            '/tests/files/sample.h5p',
            '/tests/files/sample-with-image.h5p',
            '/tests/files/sample-with-license-and-authors.h5p',
            '/tests/files/tree.jpg',
        ])
            ->filter(function ($file) use ($filename) {
                return Str::after($filename, base_path()) === $file;
            })
            ->isNotEmpty();
    }
}

namespace Tests\H5P\API {

    use App\H5PContent;
    use App\H5PLibrary;
    use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
    use App\User;
    use Cerpus\VersionClient\VersionData;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Http\Response;
    use Illuminate\Http\Testing\File;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\Storage;
    use Tests\TestCase;
    use Tests\Traits\ContentAuthorStorageTrait;
    use Tests\Traits\MockVersioningTrait;
    use Tests\Traits\WithFaker;

    /**
     * Class H5PImportControllerTest
     * @package Tests\H5P\API
     */
    class H5PImportControllerTest extends TestCase
    {
        use RefreshDatabase, MockVersioningTrait, WithFaker, ContentAuthorStorageTrait;

        protected function setUp(): void
        {
            parent::setUp();
            $this->setUpContentAuthorStorage();
        }

        private function _setUp(): void
        {
            H5PContent::factory()->create();
            H5PLibrary::factory()->create();

            $versionData = new VersionData();
            $this->setupVersion([
                'createVersion' => $versionData->populate((object) ['id' => $this->faker->uuid]),
            ]);
        }

        private function setupAdapter($isUserPublishEnabled, $isPublic)
        {
            $testAdapter = $this->createStub(H5PAdapterInterface::class);
            $testAdapter->method('isUserPublishEnabled')->willReturn($isUserPublishEnabled);
            $testAdapter->method('getAdapterName')->willReturn("UnitTest");
            $testAdapter->method('getDefaultImportPrivacy')->willReturn($isPublic);
            app()->instance(H5PAdapterInterface::class, $testAdapter);
        }

        /**
         * @test
         * @backupGlobals enabled
         */
        public function importH5P()
        {
            $this->withoutMiddleware();
            $_SERVER['REQUEST_METHOD'] = "POST";

            $this->_setUp();
            $this->setupAdapter(false, false);

            $fakeDisk = Storage::fake($this->contentAuthorStorage->getBucketDiskName());
            config(['h5p.storage.path' => $fakeDisk->path("")]);

            collect([
                [
                    "Phpunit FTW!",
                    "tests/files/sample-blanks-1.6.h5p",
                    1,
                    6,
                    '{"text":"Fill in the missing words","score":"You got @score of @total points","showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"disableImageZooming":false,"confirmCheckDialog":false,"confirmRetryDialog":false},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>Phpunit is *awesome*!<\/p>\n"]}',
                ],
                [
                    "Unit test 4 the win!",
                    "tests/files/sample.h5p",
                    1,
                    12,
                    '{"media":{"disableImageZooming":false},"text":"<p>Unit tests fill in the missing blank<\/p>\n","overallFeedback":[{"from":0,"to":100}],"showSolutions":"Show solution","tryAgain":"Retry","checkAnswer":"Check","notFilledOut":"Please fill in all blanks to view solution","answerIsCorrect":"&#039;:ans&#039; is correct","answerIsWrong":"&#039;:ans&#039; is wrong","answeredCorrectly":"Answered correctly","answeredIncorrectly":"Answered incorrectly","solutionLabel":"Correct answer:","inputLabel":"Blank input @num of @total","inputHasTipLabel":"Tip available","tipLabel":"Tip","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"autoCheck":false,"caseSensitive":true,"showSolutionsRequiresInput":true,"separateLines":false,"confirmCheckDialog":false,"confirmRetryDialog":false,"acceptSpellingErrors":false},"scoreBarLabel":"You got :num out of :total points","confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"questions":["<p>*Unittests* makes life more *easy*!<\/p>\n"]}',
                ],
            ])
                ->eachSpread(function ($title, $path, $majorVersion, $minorVersion, $expectedParameterStructure) use ($fakeDisk) {
                    $machineName = "H5P.Blanks";
                    $file = new File('sample.h5p', fopen(base_path($path), 'r'));
                    $user = User::factory()->make();
                    $parameters = [
                        'h5p' => $file,
                        'userId' => $user->auth_id,
                    ];
                    $response = $this
                        ->postJson(route('api.import.h5p'), $parameters)
                        ->assertStatus(Response::HTTP_CREATED);

                    $this->assertDatabaseHas('h5p_libraries', [
                        'name' => $machineName,
                        'major_version' => $majorVersion,
                        'minor_version' => $minorVersion,
                    ]);
                    /** @var H5PLibrary $library */
                    $library = H5PLibrary::fromLibrary([$machineName, $majorVersion, $minorVersion])->first();
                    $this->assertDatabaseHas('h5p_contents', [
                        'title' => $title,
                        'library_id' => $library->id,
                    ]);
                    $this->assertFileExists($fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getLibraryString(true))));

                    /** @var H5PContent $h5pContent */
                    $h5pContent = H5PContent::with('metadata')
                        ->where('title', $title)
                        ->where('library_id', $library->id)
                        ->first();
                    $this->assertJsonStringEqualsJsonString($expectedParameterStructure, $h5pContent->parameters);
                    $this->assertEquals('U', $h5pContent->metadata->license);
                    $this->assertTrue($h5pContent->is_published);
                    $this->assertFalse($h5pContent->isListed());

                    $responseData = $response->json();
                    $this->assertEquals($title, $responseData['title']);
                    $this->assertEquals($machineName, $responseData['h5pType']);
                });
        }

        /**
         * @test
         * @backupGlobals enabled
         */
        public function importH5PWithImage()
        {
            $this->withoutMiddleware();
            $_SERVER['REQUEST_METHOD'] = "POST";

            $this->_setUp();
            $this->setupAdapter(false, true);

            $fakeDisk = Storage::fake($this->contentAuthorStorage->getBucketDiskName());
            config(['h5p.storage.path' => $fakeDisk->path("")]);
            app()->instance('requestId', 123);
            $user = User::factory()->make();
            Session::put('authId', $user->auth_id);

            $title = "Phpunit is awesome!";
            $machineName = "H5P.MultiChoice";
            $file = new File('sample-with-image.h5p', fopen(base_path('tests/files/sample-with-image.h5p'), 'r'));
            $parameters = [
                'h5p' => $file,
                'userId' => $user->auth_id,
                'isDraft' => true,
            ];
            $response = $this
                ->postJson(route('api.import.h5p'), $parameters)
                ->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('h5p_libraries', [
                'name' => $machineName,
                'major_version' => 1,
                'minor_version' => 14,
            ]);
            /** @var H5PLibrary $library */
            $library = H5PLibrary::fromLibrary([$machineName, 1, 14])->first();
            $this->assertDatabaseHas('h5p_contents', [
                'title' => $title,
                'library_id' => $library->id,
            ]);
            $this->assertFileExists($fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getLibraryString(true))));

            $h5pContent = H5PContent::with('metadata')
                ->where('title', $title)
                ->where('library_id', $library->id)
                ->first();
            $this->assertNotNull($h5pContent);
            $this->assertJsonStringEqualsJsonString('{"media":{"type":{"params":{"contentName":"Image","file":{"path":"images\/file-5edde9091ebe0.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Hjalmar"},"subContentId":"d4c10c9b-e792-4109-9d5b-d14175f61625"},"disableImageZooming":false},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<p>Yes<\/p>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<p>No<\/p>\n"}],"overallFeedback":[{"from":0,"to":100}],"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"You got :num out of :total points","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"<p>Is phpunit awesome?<\/p>\n"}',
                $h5pContent->parameters);
            $this->assertEquals('U', $h5pContent->metadata->license);
            $this->assertFalse($h5pContent->is_published);
            $this->assertTrue($h5pContent->isListed());

            $imagePath = 'content/%s/images/file-5edde9091ebe0.jpg';
            $this->assertFileExists($fakeDisk->path(sprintf($imagePath, $h5pContent->id)));

            $responseData = $response->json();
            $this->assertEquals($title, $responseData['title']);
            $this->assertEquals($machineName, $responseData['h5pType']);
        }

        /**
         * @test
         * @backupGlobals enabled
         */
        public function importH5PWithMetadata()
        {
            $this->withoutMiddleware();
            $_SERVER['REQUEST_METHOD'] = "POST";
            $this->_setUp();
            $this->setupAdapter(true, false);

            $fakeDisk = Storage::fake($this->contentAuthorStorage->getBucketDiskName());
            config(['h5p.storage.path' => $fakeDisk->path("")]);

            $title = "Text about PhpUnit";
            $machineName = "H5P.DragText";
            $file = new File('sample-with-license-and-authors.h5p', fopen(base_path('tests/files/sample-with-license-and-authors.h5p'), 'r'));
            $user = User::factory()->make();
            $parameters = [
                'h5p' => $file,
                'userId' => $user->auth_id,
                'isPublic' => true,
            ];
            $response = $this
                ->postJson(route('api.import.h5p'), $parameters)
                ->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('h5p_libraries', [
                'name' => $machineName,
                'major_version' => 1,
                'minor_version' => 8,
            ]);
            /** @var H5PLibrary $library */
            $library = H5PLibrary::fromLibrary([$machineName, 1, 8])->first();
            $this->assertDatabaseHas('h5p_contents', [
                'title' => $title,
                'library_id' => $library->id,
            ]);
            $this->assertFileExists($fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getLibraryString(true))));

            $h5pContent = H5PContent::with('metadata')
                ->where('title', $title)
                ->where('library_id', $library->id)
                ->first();
            $this->assertNotNull($h5pContent);
            $this->assertJsonStringEqualsJsonString('{"taskDescription":"Drag the words into the correct boxes","overallFeedback":[{"from":0,"to":100}],"checkAnswer":"Check","tryAgain":"Retry","showSolution":"Show solution","dropZoneIndex":"Drop Zone @index.","empty":"Drop Zone @index is empty.","contains":"Drop Zone @index contains draggable @draggable.","ariaDraggableIndex":"@index of @count draggables.","tipLabel":"Show tip","correctText":"Correct!","incorrectText":"Incorrect!","resetDropTitle":"Reset drop","resetDropDescription":"Are you sure you want to reset this drop zone?","grabbed":"Draggable is grabbed.","cancelledDragging":"Cancelled dragging.","correctAnswer":"Correct answer:","feedbackHeader":"Feedback","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"instantFeedback":false},"scoreBarLabel":"You got :num out of :total points","textField":"*PhpUnit* is an *awesome* tool"}',
                $h5pContent->parameters);
            $this->assertFalse($h5pContent->is_published);
            $this->assertTrue($h5pContent->isListed());

            $this->assertDatabaseHas('h5p_contents_metadata', [
                'content_id' => $h5pContent->id,
            ]);

            $this->assertEquals('CC BY', $h5pContent->metadata->license);
            $this->assertEquals('Test comment', $h5pContent->metadata->author_comments);

            $responseData = $response->json();
            $this->assertEquals($title, $responseData['title']);
            $this->assertEquals($machineName, $responseData['h5pType']);
        }

        /**
         * @test
         */
        public function importH5P_invalidFile()
        {
            $this->withoutMiddleware();
            $_SERVER['REQUEST_METHOD'] = "POST";

            $fakeDisk = Storage::fake($this->contentAuthorStorage->getBucketDiskName());
            config(['h5p.storage.path' => $fakeDisk->path("")]);

            $this
                ->postJson(route('api.import.h5p'))
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(['message' => 'The given data was invalid.']);

            $file = new File('tree.jpg', fopen(base_path('tests/files/tree.jpg'), 'r'));
            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(['message' => 'The given data was invalid.']);

            $user = User::factory()->make();
            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                    'userId' => $user->auth_id,
                ])
                ->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJson(['message' => 'The file you uploaded is not a valid HTML5 Package (We are unable to unzip it)']);

            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                    'userId' => $user->auth_id,
                    'disablePublishMetadata' => $this->faker->word,
                ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(['message' => 'The given data was invalid.']);

            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                    'userId' => $user->auth_id,
                    'isPublic' => $this->faker->word,
                ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(['message' => 'The given data was invalid.']);
        }
    }
}
