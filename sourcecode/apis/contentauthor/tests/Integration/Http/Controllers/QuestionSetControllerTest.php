<?php

namespace Tests\Integration\Http\Controllers;

use App\Events\QuestionsetWasSaved;
use App\Game;
use App\Gametype;
use App\H5PLibrary;
use App\Http\Controllers\QuestionSetController;
use App\Http\Libraries\License;
use App\Http\Requests\ApiQuestionsetRequest;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Packages\QuestionSet as QuestionSetPackage;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use Faker\Provider\Uuid;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class QuestionSetControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->session([
            'authId' => Uuid::uuid(),
        ]);
    }

    public function testCreateQuestionSet(): void
    {
        $request = Request::create('', parameters: [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);
        $request->setLaravelSession(app(Session::class));

        $questionSetController = app(QuestionSetController::class);
        $result = $questionSetController->create($request);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('editorSetup', $data);
        $editorSetup = json_decode($data['editorSetup'], true);
        $this->assertIsArray($editorSetup);

        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertIsArray($state);
        $this->assertArrayHasKey('license', $state);
        $this->assertEquals(License::LICENSE_EDLIB, $state['license']);
    }

    public function testCreatePresentation(): void
    {
        Event::fake();
        $userId = $this->faker->uuid;

        $gameType = Gametype::factory()->create([
            'title' => 'MillionTest',
            'name' => Millionaire::$machineName,
        ]);

        $requestData = [
            'title' => 'Something',
            'tags' => ['list', 'of', 'tags', 'goes', 'here'],
            'license' => License::LICENSE_BY_NC_SA,
            'selectedPresentation' => Millionaire::$machineName,
            'cards' => json_decode('[{"order":1,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":2,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":3,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":4,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":5,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":6,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":7,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":8,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":9,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":10,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":11,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":12,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":13,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":14,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"order":15,"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]}]', true),
        ];

        $response = $this->withSession(['authId' => $userId, 'locale' => 'se_fi'])
            ->post('/questionset', ['questionSetJsonData' => json_encode($requestData)])
            ->assertCreated();

        $this->assertDatabaseMissing('question_sets', [
            'title' => $requestData['title'],
        ]);

        $this->assertDatabaseHas('games', [
            'gametype' => $gameType->id,
            'title' => $requestData['title'],
            'owner' => $userId,
        ]);

        /** @var Game $game */
        $game = Game::where('gameType', '=', $gameType->id)
            ->where('owner', '=', $userId)
            ->where('title', '=', $requestData['title'])
            ->firstOrFail();

        $response->assertJson([
            'url' => 'http://localhost/game/' . $game->id . '/edit',
        ]);

        $this->assertSame('en_us', $game->language_code);

        $this->assertObjectHasProperty('locale', $game->game_settings);
        $this->assertSame('se_fi', $game->game_settings->locale);
        $this->assertObjectHasProperty('questionSet', $game->game_settings);
        $this->assertObjectHasProperty('questions', $game->game_settings->questionSet);
        $this->assertCount(15, $game->game_settings->questionSet->questions);
        Event::assertNotDispatched(QuestionsetWasSaved::class);
    }

    public function testEdit(): void
    {
        $userId = $this->faker->uuid;
        $this->withSession(['authId' => $userId]);
        H5PLibrary::factory()->create([
            'name' => QuestionSetPackage::$machineName,
            'major_version' => QuestionSetPackage::$majorVersion,
            'minor_version' => QuestionSetPackage::$minorVersion,
        ]);
        Gametype::factory()->create(['name' => Millionaire::$machineName]);

        $qs = QuestionSet::factory()->create(['owner' => $userId]);
        $request = Request::create('', parameters: [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);
        $request->setLaravelSession(app(Session::class));

        $questionSetController = app(QuestionSetController::class);
        $result = $questionSetController->edit($request, $qs->id);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertIsArray($data);

        $this->assertArrayHasKey('editorSetup', $data);
        $editorSetup = json_decode($data['editorSetup'], true);
        $this->assertIsArray($editorSetup);
        $this->assertArrayHasKey('contentProperties', $editorSetup);
        $this->assertIsArray($editorSetup['contentProperties']);
        $this->assertSame(null, $editorSetup['contentProperties']['ownerName']);

        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertIsArray($state);
        $this->assertArrayHasKey('license', $state);
        $this->assertEquals('', $state['license']);

        $this->assertArrayHasKey('contentTypes', $state);
        $this->assertCount(1, $state['contentTypes']);
        $this->assertArrayHasKey('img', $state['contentTypes'][0]);
        $this->assertArrayHasKey('label', $state['contentTypes'][0]);
        $this->assertArrayHasKey('outcome', $state['contentTypes'][0]);
    }

    public function testUpdate()
    {
        Event::fake();

        /** @var Collection<QuestionSet> $questionsets */
        $questionsets = QuestionSet::factory()->count(3)
            ->create()
            ->each(function (QuestionSet $questionset) {
                /** @var QuestionSetQuestion $qsq */
                $qsq = $questionset->questions()->save(QuestionSetQuestion::factory()->make(['order' => 0]));
                $qsq->answers()->save(QuestionSetQuestionAnswer::factory()->make(['order' => 0, 'correct' => true]));
                $qsq->answers()->save(QuestionSetQuestionAnswer::factory()->make(['order' => 1, 'correct' => false]));
            });

        $this->withSession(["authId" => "user_1"]);

        /** @var QuestionSet $questionset */
        $questionset = $questionsets->random();
        /** @var QuestionSetQuestion $question */
        $question = $questionset->questions()->first();
        /** @var QuestionSetQuestionAnswer $answer */
        $answer = $question->answers()->first();
        $json = [
            'title' => "New title",
            'tags' => ['list', 'of', 'tags', 'goes', 'here'],
            'cards' => [
                (object) [
                    'id' => $question->id,
                    'order' => $question->order,
                    'canDelete' => false,
                    'image' => null,
                    'question' => ['text' => "Updated question"],
                    'answers' => [
                        (object) [
                            'id' => $answer->id,
                            'answerText' => "Updated answer",
                            'isCorrect' => (bool) $answer->correct,
                            'showToggle' => false,
                            'canDelete' => false,
                            'image' => null,
                            'order' => $answer->order,
                        ],
                    ],
                ],
            ],
        ];
        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController = app(QuestionSetController::class);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_sets', [
            'id' => $questionset->id,
            'title' => "New title",
        ])
            ->assertDatabaseHas('question_set_questions', [
                'id' => $question->id,
                'question_text' => "Updated question",
                'order' => 0,
            ])
            ->assertDatabaseHas('question_set_question_answers', [
                'id' => $answer->id,
                'answer_text' => "Updated answer",
                'order' => 0,
            ]);

        $json['cards'][] = (object) [
            'id' => $this->faker->uuid,
            'order' => ++$question->order,
            'canDelete' => false,
            'question' => ['text' => "New question"],
            'image' => null,
            'answers' => [
                (object) [
                    'id' => $this->faker->uuid,
                    'answerText' => "New correct answer",
                    'isCorrect' => true,
                    'showToggle' => false,
                    'canDelete' => false,
                    'image' => null,
                    'order' => $answer->order,
                ],
                (object) [
                    'id' => $this->faker->uuid,
                    'answerText' => "New wrong answer",
                    'isCorrect' => false,
                    'showToggle' => false,
                    'canDelete' => false,
                    'image' => null,
                    'order' => $answer->order,
                ],
            ],
        ];

        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_set_questions', [
            'question_set_id' => $questionset->id,
            'question_text' => "New question",
        ])
            ->assertDatabaseHas('question_set_question_answers', [
                'answer_text' => "New correct answer",
                'correct' => '1',
            ])
            ->assertDatabaseHas('question_set_question_answers', [
                'answer_text' => "New wrong answer",
                'correct' => 0,
            ]);

        $json['cards'][0]->answers = [
            (object) [
                'id' => $this->faker->uuid,
                'answerText' => "Added answer",
                'isCorrect' => (bool) $answer->correct,
                'showToggle' => false,
                'canDelete' => false,
                'image' => null,
                'order' => 0,
            ],
            (object) [
                'id' => $answer->id,
                'answerText' => "Updated answer",
                'isCorrect' => (bool) $answer->correct,
                'showToggle' => false,
                'canDelete' => false,
                'image' => null,
                'order' => 1,
            ],
        ];

        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_set_question_answers', [
            'question_id' => $question->id,
            'answer_text' => "Added answer",
            'order' => 0,
        ])
            ->assertDatabaseHas('question_set_question_answers', [
                'id' => $answer->id,
                'question_id' => $question->id,
                'answer_text' => "Updated answer",
                'order' => 1,
            ]);


        unset($json['cards'][0]);
        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);
        $this->assertDatabaseMissing('question_set_questions', [
            'id' => $question->id,
            'question_text' => "New question",
        ])
            ->assertDatabaseMissing('question_set_question_answers', [
                'id' => $answer->id,
                'answer_text' => "Updated answer",
            ]);

        Event::assertDispatched(QuestionsetWasSaved::class);
    }

    public function testUpdateWithMath()
    {
        Event::fake();

        /** @var Collection<QuestionSet> $questionsets */
        $questionsets = QuestionSet::factory()->count(3)
            ->create()
            ->each(function (QuestionSet $questionset, $index) {
                $questionset->questions()
                    ->save(QuestionSetQuestion::factory()->make(['order' => $index]))
                    ->each(function (QuestionSetQuestion $question, $index) {
                        $question
                            ->answers()
                            ->save(QuestionSetQuestionAnswer::factory()->make(['order' => $index]));
                    });
            });

        $this->withSession(["authId" => "user_1"]);

        /** @var QuestionSet $questionset */
        $questionset = $questionsets->random();
        /** @var QuestionSetQuestion $question */
        $question = $questionset->questions()->first();
        /** @var QuestionSetQuestionAnswer $answer */
        $answer = $question->answers()->first();
        $json = [
            'title' => "New title",
            'tags' => ['list', 'of', 'tags', 'goes', 'here'],
            'license' => 'BY',
            'cards' => [
                (object) [
                    'id' => $question->id,
                    'order' => $question->order,
                    'canDelete' => false,
                    'image' => null,
                    'question' => [
                        'text' => '<p>Albert Einstein formula: <span class="math_container">\(E=mc^2\)</span></p>',
                    ],
                    'answers' => [
                        (object) [
                            'id' => $answer->id,
                            'answerText' => '<p>The well known Pythagorean theorem \(x^2 + y^2 = z^2\) was proved to be invalid for other exponents.<span class="math_container">\(x^n + y^n = z^n\)</span></p>',
                            'isCorrect' => (bool) $answer->correct,
                            'showToggle' => false,
                            'canDelete' => false,
                            'image' => null,
                            'order' => $answer->order,
                        ],
                    ],
                ],
            ],
        ];
        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController = app(QuestionSetController::class);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_sets', [
            'id' => $questionset->id,
            'title' => "New title",
        ])
            ->assertDatabaseHas('question_set_questions', [
                'id' => $question->id,
                'question_text' => '<p>Albert Einstein formula: $$E=mc^2$$</p>',
                'order' => 0,
            ])
            ->assertDatabaseHas('question_set_question_answers', [
                'id' => $answer->id,
                'answer_text' => '<p>The well known Pythagorean theorem \(x^2 + y^2 = z^2\) was proved to be invalid for other exponents.$$x^n + y^n = z^n$$</p>',
                'order' => 0,
            ]);

        $json['cards'][] = (object) [
            'id' => $this->faker->uuid,
            'order' => ++$question->order,
            'canDelete' => false,
            'question' => ['text' => "New question"],
            'image' => null,
            'answers' => [
                (object) [
                    'id' => $this->faker->uuid,
                    'answerText' => "New correct answer",
                    'isCorrect' => true,
                    'showToggle' => false,
                    'canDelete' => false,
                    'image' => null,
                    'order' => $answer->order,
                ],
                (object) [
                    'id' => $this->faker->uuid,
                    'answerText' => "New wrong answer",
                    'isCorrect' => false,
                    'showToggle' => false,
                    'canDelete' => false,
                    'image' => null,
                    'order' => $answer->order,
                ],
            ],
        ];

        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_set_questions', [
            'question_set_id' => $questionset->id,
            'question_text' => "New question",
        ])
            ->assertDatabaseHas('question_set_question_answers', [
                'answer_text' => "New correct answer",
                'correct' => '1',
            ])
            ->assertDatabaseHas('question_set_question_answers', [
                'answer_text' => "New wrong answer",
                'correct' => 0,
            ]);

        $json['cards'][0]->answers = [
            (object) [
                'id' => $this->faker->uuid,
                'answerText' => "Added answer",
                'isCorrect' => (bool) $answer->correct,
                'showToggle' => false,
                'canDelete' => false,
                'image' => null,
                'order' => 0,
            ],
            (object) [
                'id' => $answer->id,
                'answerText' => "Updated answer",
                'isCorrect' => (bool) $answer->correct,
                'showToggle' => false,
                'canDelete' => false,
                'image' => null,
                'order' => 1,
            ],
        ];

        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);

        $this->assertDatabaseHas('question_set_question_answers', [
            'question_id' => $question->id,
            'answer_text' => "Added answer",
            'order' => 0,
        ])
            ->assertDatabaseHas('question_set_question_answers', [
                'id' => $answer->id,
                'question_id' => $question->id,
                'answer_text' => "Updated answer",
                'order' => 1,
            ]);


        unset($json['cards'][0]);
        $request = new ApiQuestionsetRequest([], ['questionSetJsonData' => json_encode($json)]);
        $questionsetController->update($request, $questionset);
        $this->assertDatabaseMissing('question_set_questions', [
            'id' => $question->id,
            'question_text' => "New question",
        ])
            ->assertDatabaseMissing('question_set_question_answers', [
                'id' => $answer->id,
                'answer_text' => "Updated answer",
            ]);
        Event::assertDispatched(QuestionsetWasSaved::class);
    }

    public function testUpdateFullRequest()
    {
        Event::fake();

        $testAdapter = $this->createStub(H5PAdapterInterface::class);
        $testAdapter->method('getAdapterName')->willReturn("UnitTest");
        app()->instance(H5PAdapterInterface::class, $testAdapter);

        $json = [
            'title' => "New title",
            'tags' => ['list', 'of', 'tags', 'goes', 'here'],
            'cards' => [
                (object) [
                    'order' => 1,
                    'canDelete' => false,
                    'image' => [],
                    'question' => [
                        'text' => "New question",
                        'image' => null,
                    ],
                    'answers' => [
                        (object) [
                            'answerText' => "New answer",
                            'isCorrect' => true,
                            'showToggle' => false,
                            'canDelete' => false,
                            'image' => [],
                            'order' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $authId = Str::uuid();
        $this->withSession(["authId" => $authId])
            ->post(route('questionset.store'), [
                'title' => "New title",
                'license' => "BY",
                'questionSetJsonData' => json_encode($json),
                'share' => 'PRIVATE',
            ])
            ->assertStatus(Response::HTTP_CREATED);

        $this->assertDatabaseHas('question_sets', [
            'title' => "New title",
            "tags" => "list,of,tags,goes,here",
            'license' => 'BY',
        ]);

        /** @var QuestionSet $storedQuestionSet */
        $storedQuestionSet = QuestionSet::where('title', 'New title')->first();

        $json['title'] = "Updated title";
        $this->withSession(["authId" => $authId])
            ->put(route('questionset.update', $storedQuestionSet->id), [
                'license' => "BY",
                'questionSetJsonData' => json_encode($json),
                'share' => 'PRIVATE',
            ])
            ->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('question_sets', [
            'title' => "Updated title",
            "tags" => "list,of,tags,goes,here",
            'license' => 'BY',
        ]);
        Event::assertDispatched(QuestionsetWasSaved::class);
    }
}
