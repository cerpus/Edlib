<?php

namespace Tests\API\Handler;


use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Packages\MultiChoice;
use Cerpus\CoreClient\DataObjects\Answer;
use Cerpus\CoreClient\DataObjects\MultiChoiceQuestion;
use Cerpus\CoreClient\DataObjects\Questionset;
use Cerpus\VersionClient\VersionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\db\TestH5PSeeder;
use Tests\TestCase;
use Tests\Traits\MockResourceApi;
use Tests\Traits\MockVersioningTrait;
use Tests\Traits\ResetH5PStatics;
use Tests\Traits\WithFaker;

class ContentTypeHandlerTest extends TestCase
{

    use RefreshDatabase, MockVersioningTrait, WithFaker, ResetH5PStatics, MockResourceApi;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(TestH5PSeeder::class);
        $versionData = new VersionData();
        $this->setupVersion([
            'createVersion' => $versionData->populate((object) ['id' => $this->faker->uuid]),
        ]);
    }

    /**
     * @test
     *
     * Uses a manually created array for values to test older structures
     */
    public function createNewQuestionSetFromArray_validData_thenSuccess()
    {
        $this->setUpResourceApi();

        $handler = new ContentTypeHandler();
        $this->isInstanceOf(ContentTypeHandler::class);

        $authId = $this->faker->uuid;
        $title = $this->faker->sentence;
        $question = $this->faker->sentence;
        $options = $this->faker->sentences(3);

        $data = [
            'authId' => $authId,
            'license' => "BY",
            'sharing' => 'private',
            'title' => $title,
            'questions' => [
                [
                    'type' => MultiChoice::$machineName,
                    'text' => $question,
                    'answers' => [
                        [
                            'text' => $options[0],
                            'correct' => true,
                        ],
                        [
                            'text' => $options[1],
                            'correct' => false,
                        ],
                        [
                            'text' => $options[2],
                            'correct' => true,
                        ],
                    ]
                ]
            ]
        ];

        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'is_private' => 1,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);

        $data['sharing'] = true;
        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'is_private' => 1,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
    }

    /**
     * @test
     *
     * Uses a manually created array for values to test older structures
     */
    public function createNewQuestionSetFromArrayWithDraftLogic_validData_thenSuccess()
    {
        $this->setUpResourceApi();

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('enableDraftLogic')->willReturn(true);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);


        $handler = new ContentTypeHandler();
        $this->isInstanceOf(ContentTypeHandler::class);

        $authId = $this->faker->uuid;
        $title = $this->faker->sentence;
        $question = $this->faker->sentence;
        $options = $this->faker->sentences(3);

        $data = [
            'authId' => $authId,
            'license' => "BY",
            'sharing' => 'private',
            'published' => 0,
            'title' => $title,
            'questions' => [
                [
                    'type' => MultiChoice::$machineName,
                    'text' => $question,
                    'answers' => [
                        [
                            'text' => $options[0],
                            'correct' => true,
                        ],
                        [
                            'text' => $options[1],
                            'correct' => false,
                        ],
                        [
                            'text' => $options[2],
                            'correct' => true,
                        ],
                    ]
                ]
            ]
        ];

        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'is_private' => 1,
            'is_published' => 0,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);

        $data['sharing'] = true;
        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'is_private' => 1,
            'max_score' => null,
            'bulk_calculated' => 0,
            'is_published' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
    }


    /**
     * @test
     */
    public function createNewQuestionSetFromClient_validData_thenSuccess()
    {
        $this->setUpResourceApi();
        $handler = new ContentTypeHandler();

        $authId = $this->faker->uuid;
        $title = $this->faker->sentence;
        $questionText = $this->faker->sentence;
        $options = $this->faker->sentences(3);

        $questionset = Questionset::create([
            'authId' => $authId,
            'license' => "BY",
            'title' => $title
        ]);
        $answers = collect([
            Answer::create([
                'text' => $options[0],
                'correct' => true,
            ]),
            Answer::create([
                'text' => $options[1],
                'correct' => false,
            ]),
            Answer::create([
                'text' => $options[2],
                'correct' => true,
            ]),
        ]);

        /** @var MultiChoiceQuestion $question */
        $question = MultiChoiceQuestion::create([
            'text' => $questionText
        ]);
        $question->addAnswers($answers);
        $questionset->addQuestion($question);

        $content = $handler->storeQuestionset($questionset->toArray());
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', ["title" => $title, 'user_id' => $authId, 'is_private' => 1, 'max_score' => 2]);
        $this->assertArrayHasKey("id", $content);

        $questionset->setSharing(true);
        $content = $handler->storeQuestionset($questionset->toArray());
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', ["title" => $title, 'user_id' => $authId, 'is_private' => 0, 'max_score' => 2]);
        $this->assertArrayHasKey("id", $content);
    }
}
