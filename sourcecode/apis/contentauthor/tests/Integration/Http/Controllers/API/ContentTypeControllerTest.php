<?php

namespace Tests\Integration\Http\Controllers\API;

use App\H5PContent;
use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Libraries\H5P\Packages\MultiChoice;
use App\Libraries\H5P\Packages\QuestionSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class ContentTypeControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(TestH5PSeeder::class);
    }

    #[Test]
    public function ContentTypeController_validRequest_thenSuccess()
    {
        $authId = $this->faker->uuid;
        $title = $this->faker->sentence;
        $question = $this->faker->sentence;
        $options = $this->faker->sentences(3);
        $content = H5PContent::factory()->create();

        $contentHandler = $this->createPartialMock(ContentTypeHandler::class, [
            'storeQuestionset',
        ]);
        $contentHandler->method('storeQuestionset')->willReturn($content);
        app()->instance(ContentTypeHandler::class, $contentHandler);

        $data = [
            'authId' => $authId,
            'license' => "BY",
            'sharing' => false,
            'title' => $title,
            'type' => QuestionSet::$machineName,
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
                    ],
                ],
            ],
        ];

        $response = $this->postJson("api/v1/contenttypes/questionsets", $data);
        $response
            ->assertSuccessful()
            ->assertExactJson([
                'id' => $content->id,
                'type' => QuestionSet::$machineName,
            ]);
    }

    #[Test]
    public function ContentTypeConrollerJSON_invalidData_thenFailure()
    {
        $this->postJson("api/v1/contenttypes/questionsets")
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([ // Test "shape" of response
                'message' => true,
                'errors' => [
                    'title' => [true],
                    'license' => [true],
                    'questions' => [true],
                ],
            ]);
    }

    #[Test]
    public function ContentTypeConrollerPOST_invalidData_thenFailure()
    {
        $this->post("api/v1/contenttypes/questionsets")
            ->assertStatus(Response::HTTP_FOUND);
    }
}
