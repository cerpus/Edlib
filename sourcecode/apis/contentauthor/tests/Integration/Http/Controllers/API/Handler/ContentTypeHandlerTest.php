<?php

namespace Tests\Integration\Http\Controllers\API\Handler;

use App\Content;
use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Packages\MultiChoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\Seeds\TestH5PSeeder;
use Tests\TestCase;

class ContentTypeHandlerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(TestH5PSeeder::class);
    }

    /**
     * Uses a manually created array for values to test older structures
     */
    #[Test]
    public function createNewQuestionSetFromArray_validData_thenSuccess()
    {
        $handler = new ContentTypeHandler();
        $this->isInstanceOf(ContentTypeHandler::class);

        $authId = $this->faker->uuid;
        $title = $this->faker->sentence;
        $question = $this->faker->sentence;
        $options = $this->faker->sentences(3);

        $data = [
            'authId' => $authId,
            'license' => "BY",
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
                    ],
                ],
            ],
        ];

        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
        $this->assertDatabaseHas('content_versions', [
            'content_id' => $content->id,
            'content_type' => Content::TYPE_H5P,
        ]);

        $data['sharing'] = true;
        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
        $this->assertDatabaseHas('content_versions', [
            'content_id' => $content->id,
            'content_type' => Content::TYPE_H5P,
        ]);
    }

    /**
     * Uses a manually created array for values to test older structures
     */
    #[Test]
    public function createNewQuestionSetFromArrayWithUserPublish_validData_thenSuccess()
    {
        $testAdapter = $this->createStub(H5PAdapterInterface::class);
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
                    ],
                ],
            ],
        ];

        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
        $this->assertDatabaseHas('content_versions', [
            'content_id' => $content->id,
            'content_type' => Content::TYPE_H5P,
        ]);

        $content = $handler->storeQuestionset($data);
        $this->assertNotEmpty($content);
        $this->assertDatabaseHas('h5p_contents', [
            'title' => $title,
            'user_id' => $authId,
            'max_score' => null,
            'bulk_calculated' => 0,
        ]);
        $this->assertArrayHasKey("id", $content);
        $this->assertDatabaseHas('content_versions', [
            'content_id' => $content->id,
            'content_type' => Content::TYPE_H5P,
        ]);
    }
}
