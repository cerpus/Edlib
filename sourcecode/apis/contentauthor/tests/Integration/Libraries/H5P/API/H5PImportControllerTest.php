<?php

namespace App\Libraries\H5P {
    function is_uploaded_file($filename)
    {
        $prefix = realpath(__DIR__ . '/../../../../files');

        return in_array($filename, array_map(fn($name) => "$prefix/$name", [
            'sample-blanks-1.6.h5p',
            'sample.h5p',
            'sample-with-image.h5p',
            'sample-with-license-and-authors.h5p',
            'tree.jpg',
        ]));
    }
}

namespace Tests\Integration\Libraries\H5P\API {
    use App\Content;
    use App\H5PContent;
    use App\H5PLibrary;
    use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
    use App\User;
    use Illuminate\Contracts\Filesystem\Filesystem;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    use Illuminate\Foundation\Testing\WithFaker;
    use Illuminate\Http\Response;
    use Illuminate\Http\Testing\File;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\Storage;
    use PHPUnit\Framework\Attributes\BackupGlobals;
    use PHPUnit\Framework\Attributes\Test;
    use Tests\TestCase;

    use function base_path;
    use function fopen;

    class H5PImportControllerTest extends TestCase
    {
        use RefreshDatabase;
        use WithFaker;

        private Filesystem $fakeDisk;

        protected function setUp(): void
        {
            parent::setUp();

            $this->withoutMiddleware();
            $_SERVER['REQUEST_METHOD'] = "POST";

            $this->fakeDisk = Storage::fake();
            config(['h5p.storage.path' => $this->fakeDisk->path("")]);
        }

        private function _setUp(): void
        {
            H5PContent::factory()->create();
            H5PLibrary::factory()->create();
        }

        private function setupAdapter()
        {
            $testAdapter = $this->createStub(H5PAdapterInterface::class);
            $testAdapter->method('getAdapterName')->willReturn("UnitTest");
            app()->instance(H5PAdapterInterface::class, $testAdapter);
        }

        #[BackupGlobals(true)]
        #[Test]
        public function importH5P()
        {
            $this->_setUp();
            $this->setupAdapter();

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
                ->eachSpread(function ($title, $path, $majorVersion, $minorVersion, $expectedParameterStructure) {
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
                    $this->assertFileExists($this->fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getFolderName())));

                    /** @var H5PContent $h5pContent */
                    $h5pContent = H5PContent::with('metadata')
                        ->where('title', $title)
                        ->where('library_id', $library->id)
                        ->first();
                    $this->assertJsonStringEqualsJsonString($expectedParameterStructure, $h5pContent->parameters);
                    $this->assertEquals('U', $h5pContent->metadata->license);
                    $this->assertDatabaseHas('content_versions', [
                        'id' => $h5pContent->version_id,
                        'content_id' => $h5pContent->id,
                        'content_type' => Content::TYPE_H5P,
                        'parent_id' => null,
                    ]);

                    $responseData = $response->json();
                    $this->assertEquals($title, $responseData['title']);
                    $this->assertEquals($machineName, $responseData['h5pType']);
                });
        }

        #[BackupGlobals(true)]
        #[Test]
        public function importH5PWithImage()
        {
            $this->_setUp();
            $this->setupAdapter();

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
            $this->assertFileExists($this->fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getFolderName())));

            /** @var H5PContent $h5pContent */
            $h5pContent = H5PContent::with('metadata')
                ->where('title', $title)
                ->where('library_id', $library->id)
                ->first();
            $this->assertNotNull($h5pContent);
            $this->assertJsonStringEqualsJsonString(
                '{"media":{"type":{"params":{"contentName":"Image","file":{"path":"images\/file-5edde9091ebe0.jpg","mime":"image\/jpeg","copyright":{"license":"U"},"width":196,"height":358}},"library":"H5P.Image 1.1","metadata":{"contentType":"Image","license":"U","title":"Hjalmar"},"subContentId":"d4c10c9b-e792-4109-9d5b-d14175f61625"},"disableImageZooming":false},"answers":[{"correct":true,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<p>Yes<\/p>\n"},{"correct":false,"tipsAndFeedback":{"tip":"","chosenFeedback":"","notChosenFeedback":""},"text":"<p>No<\/p>\n"}],"overallFeedback":[{"from":0,"to":100}],"behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"type":"auto","singlePoint":false,"randomAnswers":true,"showSolutionsRequiresInput":true,"confirmCheckDialog":false,"confirmRetryDialog":false,"autoCheck":false,"passPercentage":100,"showScorePoints":true},"UI":{"checkAnswerButton":"Check","showSolutionButton":"Show solution","tryAgainButton":"Retry","tipsLabel":"Show tip","scoreBarLabel":"You got :num out of :total points","tipAvailable":"Tip available","feedbackAvailable":"Feedback available","readFeedback":"Read feedback","wrongAnswer":"Wrong answer","correctAnswer":"Correct answer","shouldCheck":"Should have been checked","shouldNotCheck":"Should not have been checked","noInput":"Please answer before viewing the solution"},"confirmCheck":{"header":"Finish ?","body":"Are you sure you wish to finish ?","cancelLabel":"Cancel","confirmLabel":"Finish"},"confirmRetry":{"header":"Retry ?","body":"Are you sure you wish to retry ?","cancelLabel":"Cancel","confirmLabel":"Confirm"},"question":"<p>Is phpunit awesome?<\/p>\n"}',
                $h5pContent->parameters,
            );
            $this->assertEquals('U', $h5pContent->metadata->license);

            $imagePath = 'content/%s/images/file-5edde9091ebe0.jpg';
            $this->assertFileExists($this->fakeDisk->path(sprintf($imagePath, $h5pContent->id)));

            $this->assertDatabaseHas('content_versions', [
                'id' => $h5pContent->version_id,
                'content_id' => $h5pContent->id,
                'content_type' => Content::TYPE_H5P,
                'parent_id' => null,
            ]);

            $responseData = $response->json();
            $this->assertEquals($title, $responseData['title']);
            $this->assertEquals($machineName, $responseData['h5pType']);
        }

        #[BackupGlobals(true)]
        #[Test]
        public function importH5PWithMetadata()
        {
            $this->_setUp();
            $this->setupAdapter();

            $title = "Text about PhpUnit";
            $machineName = "H5P.DragText";
            $file = new File('sample-with-license-and-authors.h5p', fopen(base_path('tests/files/sample-with-license-and-authors.h5p'), 'r'));
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
                'major_version' => 1,
                'minor_version' => 8,
            ]);
            /** @var H5PLibrary $library */
            $library = H5PLibrary::fromLibrary([$machineName, 1, 8])->first();
            $this->assertDatabaseHas('h5p_contents', [
                'title' => $title,
                'library_id' => $library->id,
            ]);
            $this->assertFileExists($this->fakeDisk->path(sprintf("libraries/%s/semantics.json", $library->getFolderName())));

            /** @var H5PContent $h5pContent */
            $h5pContent = H5PContent::with('metadata')
                ->where('title', $title)
                ->where('library_id', $library->id)
                ->first();
            $this->assertNotNull($h5pContent);
            $this->assertJsonStringEqualsJsonString(
                '{"taskDescription":"Drag the words into the correct boxes","overallFeedback":[{"from":0,"to":100}],"checkAnswer":"Check","tryAgain":"Retry","showSolution":"Show solution","dropZoneIndex":"Drop Zone @index.","empty":"Drop Zone @index is empty.","contains":"Drop Zone @index contains draggable @draggable.","ariaDraggableIndex":"@index of @count draggables.","tipLabel":"Show tip","correctText":"Correct!","incorrectText":"Incorrect!","resetDropTitle":"Reset drop","resetDropDescription":"Are you sure you want to reset this drop zone?","grabbed":"Draggable is grabbed.","cancelledDragging":"Cancelled dragging.","correctAnswer":"Correct answer:","feedbackHeader":"Feedback","behaviour":{"enableRetry":true,"enableSolutionsButton":true,"enableCheckButton":true,"instantFeedback":false},"scoreBarLabel":"You got :num out of :total points","textField":"*PhpUnit* is an *awesome* tool"}',
                $h5pContent->parameters,
            );

            $this->assertDatabaseHas('h5p_contents_metadata', [
                'content_id' => $h5pContent->id,
            ]);

            $this->assertDatabaseHas('content_versions', [
                'id' => $h5pContent->version_id,
                'content_id' => $h5pContent->id,
                'content_type' => Content::TYPE_H5P,
                'parent_id' => null,
            ]);

            $this->assertEquals('CC BY', $h5pContent->metadata->license);
            $this->assertEquals('Test comment', $h5pContent->metadata->author_comments);

            $responseData = $response->json();
            $this->assertEquals($title, $responseData['title']);
            $this->assertEquals($machineName, $responseData['h5pType']);
        }

        public function testFailsOnMissingPostData(): void
        {
            $this
                ->postJson(route('api.import.h5p'))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['h5p', 'userId']);
        }

        public function testFailsOnMissingUserId(): void
        {
            $file = new File('tree.jpg', fopen(base_path('tests/files/tree.jpg'), 'r'));
            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['userId']);
        }

        public function testFailsOnInvalidH5pFile(): void
        {
            $file = new File('tree.jpg', fopen(base_path('tests/files/tree.jpg'), 'r'));
            $user = User::factory()->make();

            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                    'userId' => $user->auth_id,
                ])
                ->assertStatus(Response::HTTP_BAD_REQUEST)
                ->assertJson(['message' => 'The file you uploaded is not a valid HTML5 Package (We are unable to unzip it)']);
        }

        public function testFailsOnInvalidIsPublicFlag(): void
        {
            $file = new File('tree.jpg', fopen(base_path('tests/files/tree.jpg'), 'r'));
            $user = User::factory()->make();

            $this
                ->postJson(route('api.import.h5p'), [
                    'h5p' => $file,
                    'userId' => $user->auth_id,
                    'isPublic' => 'invalid',
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['isPublic']);
        }
    }
}
