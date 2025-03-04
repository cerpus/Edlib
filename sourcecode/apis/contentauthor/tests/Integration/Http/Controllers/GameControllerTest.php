<?php

namespace Tests\Integration\Http\Controllers;

use App\Game;
use App\Gametype;
use App\Http\Controllers\GameController;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\Games\Millionaire\Millionaire;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\Helpers\LtiHelper;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use LtiHelper;
    use RefreshDatabase;
    use WithFaker;

    public function testEdit(): void
    {
        $userId = $this->faker->uuid;
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $game = Game::factory()->create([
            'owner' => $userId,
            'gametype' => $gameType->id,
            'game_settings' => '{"questionSet":{"questions":[{"questionText":"<p>&Eacute;n million<\/p>\n","image":null,"answers":[{"answer":"<p>1000000<\/p>\n","isCorrect":true},{"answer":"<p>100000<\/p>\n","isCorrect":false},{"answer":"<p>10000000<\/p>\n","isCorrect":false},{"answer":"<p>10000<\/p>\n","isCorrect":false}]},{"questionText":"<p>Hvilken er <strong>HTML<\/strong>?<\/p>\n","image":null,"answers":[{"answer":"<p>&lt;p&gt;<strong>Paragraph<\/strong>&lt;\/p&gt;<\/p>\n","isCorrect":true},{"answer":"<p><em>nano -LN<\/em> README.md<\/p>\n","isCorrect":false},{"answer":"<p>$html = <strong>true<\/strong>;<\/p>\n","isCorrect":false},{"answer":"<p><strong>#CDCDCD<\/strong><\/p>\n","isCorrect":false}]},{"questionText":"Vil du bli million\u00e6r?","image":null,"answers":[{"answer":"Ja","isCorrect":true},{"answer":"Nei","isCorrect":false},{"answer":"Kanskje","isCorrect":false},{"answer":"Vet ikke","isCorrect":false}]},{"questionText":"2","image":null,"answers":[{"answer":"2","isCorrect":true},{"answer":"3","isCorrect":false},{"answer":"4","isCorrect":false},{"answer":"5","isCorrect":false}]},{"questionText":"Hvor mange sp\u00f8rsm\u00e5l er p\u00e5krevd?","image":null,"answers":[{"answer":"15","isCorrect":true},{"answer":"10","isCorrect":false},{"answer":"5","isCorrect":false},{"answer":"20","isCorrect":false}]},{"questionText":"Er du million\u00e6r n\u00e5?","image":null,"answers":[{"answer":"Nei","isCorrect":true},{"answer":"Ja","isCorrect":false},{"answer":"Kanskje","isCorrect":false},{"answer":"Vet ikke","isCorrect":false}]},{"questionText":"Hvor mange tall er det i \u00e9n million?","image":null,"answers":[{"answer":"7","isCorrect":true},{"answer":"6","isCorrect":false},{"answer":"5","isCorrect":false},{"answer":"8","isCorrect":false}]},{"questionText":"5","image":null,"answers":[{"answer":"5","isCorrect":true},{"answer":"6","isCorrect":false},{"answer":"7","isCorrect":false},{"answer":"8","isCorrect":false}]},{"questionText":"1","image":null,"answers":[{"answer":"1","isCorrect":true},{"answer":"2","isCorrect":false},{"answer":"3","isCorrect":false},{"answer":"4","isCorrect":false}]},{"questionText":"Hvor mage sp\u00f8rsm\u00e5l mangler n\u00e5?","image":null,"answers":[{"answer":"<p>Ingen<\/p>\n","isCorrect":true},{"answer":"6","isCorrect":false},{"answer":"8","isCorrect":false},{"answer":"5","isCorrect":false}]},{"questionText":"Hvilket sp\u00f8rsm\u00e5l er dette?","image":null,"answers":[{"answer":"3","isCorrect":false},{"answer":"1","isCorrect":false},{"answer":"2","isCorrect":false},{"answer":"<p>11<\/p>\n","isCorrect":true}]},{"questionText":"3","image":null,"answers":[{"answer":"3","isCorrect":true},{"answer":"4","isCorrect":false},{"answer":"5","isCorrect":false},{"answer":"6","isCorrect":false}]},{"questionText":"6","image":null,"answers":[{"answer":"6","isCorrect":true},{"answer":"7","isCorrect":false},{"answer":"8","isCorrect":false},{"answer":"9","isCorrect":false}]},{"questionText":"Mange bekker sm\u00e5, ...","image":null,"answers":[{"answer":"gj\u00f8r en stor \u00c5","isCorrect":true},{"answer":"gj\u00f8r en v\u00e5t p\u00e5 beina","isCorrect":false},{"answer":"etter mye regn","isCorrect":false},{"answer":"er bare irriterende","isCorrect":false}]},{"questionText":"4","image":null,"answers":[{"answer":"4","isCorrect":true},{"answer":"5","isCorrect":false},{"answer":"6","isCorrect":false},{"answer":"7","isCorrect":false}]}]},"locale":"nb-no"}',
            'license' => 'AllMine!',
        ]);

        $request = Request::create('', parameters: [
            'lti_version' => 'LTI-1p0',
            'lti_message_type' => 'basic-lti-launch-request',
            'resource_link_id' => 'random_link_9364f20a-a9b5-411a-8f60-8a4050f85d91',
            'launch_presentation_return_url' => "https://api.edlib.test/lti/v2/editors/contentauthor/return",
            'ext_user_id' => "1",
            'launch_presentation_locale' => "nb",
        ]);
        $request->setLaravelSession(app(Session::class));
        $gameController = app(GameController::class);
        $result = $gameController->edit($request, $game->id);

        $this->assertNotEmpty($result);
        $this->assertInstanceOf(View::class, $result);
        $data = $result->getData();
        $this->assertIsArray($data);

        $this->assertArrayHasKey('state', $data);
        $state = json_decode($data['state'], true);
        $this->assertEquals('AllMine!', $state['license']);
        $this->assertNotEmpty($state['questionset']);

        $this->assertArrayHasKey('game', $data);
        $this->assertInstanceOf(Game::class, $data['game']);
        $this->assertSame($game->id, $data['game']->id);
        $this->assertSame($game->license, $data['game']->license);

        $this->assertArrayHasKey('editorSetup', $data);
        $editorSetup = json_decode($data['editorSetup'], true);
        $this->assertIsArray($editorSetup);
        $this->assertArrayHasKey('contentProperties', $editorSetup);
        $this->assertIsArray($editorSetup['contentProperties']);
        $this->assertSame(null, $editorSetup['contentProperties']['ownerName']);
    }

    public function testUpdate(): void
    {
        $userId = $this->faker->uuid;

        $this->session([
            'authId' => $userId,
        ]);
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $game = Game::factory()->create([
            'owner' => $userId,
            'gametype' => $gameType->id,
            'license' => License::LICENSE_EDLIB,
        ]);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'license' => License::LICENSE_EDLIB,
            'game_settings' => '{"setting":true}',
        ]);

        $request = [
            'title' => 'Something',
            'tags' => [],
            'license' => License::LICENSE_BY_NC,
            'questionSetJsonData' => '{"selectedPresentation":"' . Millionaire::$machineName . '"}',
            'cards' => json_decode('[{"question":{"text":"Updated question","image":{"id":"42"}},"answers":[{"answerText":"First answer","isCorrect":true,"image":{"id":"42"}},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]}]', true),
        ];

        $newUrl = $this->call('patch', '/game/' . $game->id, $request)
            ->assertOk()
            ->assertJsonStructure([
                'url',
            ])
            ->json('url');

        $this->assertDatabaseHas('games', [
            'title' => 'Something',
            'license' => License::LICENSE_BY_NC,
            'game_settings' => '{"questionSet":{"questions":[{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]},{"questionText":"Updated question","image":null,"answers":[{"answer":"First answer","isCorrect":true},{"answer":"Next answer","isCorrect":false},{"answer":"Another answer","isCorrect":false},{"answer":"Last answer","isCorrect":false}]}]},"locale":"nb-no"}',
        ]);
    }

    public function testCreate(): void
    {
        $url = 'http://localhost/game/create/millionaire';

        $response = $this->withSession(['locale' => 'se-fi'])
            ->post($url, $this->getSignedLtiParams($url, [
                'lti_version' => 'LTI-1p0',
                'lti_message_type' => 'basic-lti-launch-request',
            ]))
            ->assertOk()
            ->getOriginalContent();

        $this->assertInstanceOf(View::class, $response);
        $data = $response->getData();

        $this->assertArrayHasKey('emails', $data);
        $this->assertArrayHasKey('editorSetup', $data);
        $this->assertArrayHasKey('state', $data);

        /** @var EditorConfigObject $editorSetup */
        $editorSetup = json_decode($data['editorSetup'], flags: JSON_THROW_ON_ERROR);

        $this->assertTrue($editorSetup->canList);
        $this->assertSame('se-fi', $editorSetup->editorLanguage);

        /** @var QuestionSetStateDataObject $state */
        $state = json_decode($data['state'], flags: JSON_THROW_ON_ERROR);
        $this->assertSame(15, $state->numberOfDefaultQuestions);
        $this->assertSame(4, $state->numberOfDefaultAnswers);
        $this->assertFalse($state->canAddRemoveQuestion);
        $this->assertFalse($state->canAddRemoveAnswer);
        $this->assertSame(Millionaire::$machineName, $state->lockedPresentation);
    }

    public function testView(): void
    {
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $game = Game::factory()->create([
            'title' => 'The millionaire',
            'owner' => $this->faker->uuid,
            'gametype' => $gameType->id,
            'game_settings' => '{"questionSet":{"questions":[{"questionText":"C 1","image":"/fake/image/42","answers":[{"answer":"C 1-1","isCorrect":true,"image":"/fake/image/42"},{"answer":"C 1-2","isCorrect":false},{"answer":"C 1-3","isCorrect":false},{"answer":"C 1-4","isCorrect":false}]},{"questionText":"C 2","image":null,"answers":[{"answer":"C 2-1","isCorrect":true},{"answer":"C 2-2","isCorrect":false},{"answer":"C 2-3","isCorrect":false},{"answer":"C 2-4","isCorrect":false}]},{"questionText":"C 3","image":null,"answers":[{"answer":"C 3-1","isCorrect":true},{"answer":"C 3-2","isCorrect":false},{"answer":"C 3-3","isCorrect":false},{"answer":"C 3-4","isCorrect":false}]},{"questionText":"C 4","image":null,"answers":[{"answer":"C 4-1","isCorrect":true},{"answer":"C 4-2","isCorrect":false},{"answer":"C 4-3","isCorrect":false},{"answer":"C 4-4","isCorrect":false}]},{"questionText":"C 5","image":null,"answers":[{"answer":"C 5-1","isCorrect":true},{"answer":"C 5-2","isCorrect":false},{"answer":"C 5-3","isCorrect":false},{"answer":"C 5-4","isCorrect":false}]},{"questionText":"C 6","image":null,"answers":[{"answer":"C 6-1","isCorrect":true},{"answer":"C 6-2","isCorrect":false},{"answer":"C 6-3","isCorrect":false},{"answer":"C 6-4","isCorrect":false}]},{"questionText":"C 7","image":null,"answers":[{"answer":"C 7-1","isCorrect":true},{"answer":"C 7-2","isCorrect":false},{"answer":"C 7-3","isCorrect":false},{"answer":"C 7-4","isCorrect":false}]},{"questionText":"C 8","image":null,"answers":[{"answer":"C 8-1","isCorrect":true},{"answer":"C 8-2","isCorrect":false},{"answer":"C 8-3","isCorrect":false},{"answer":"C 8-4","isCorrect":false}]},{"questionText":"C 9","image":null,"answers":[{"answer":"C 9-1","isCorrect":true},{"answer":"C 9-2","isCorrect":false},{"answer":"C 9-3","isCorrect":false},{"answer":"C 9-4","isCorrect":false}]},{"questionText":"C 10","image":null,"answers":[{"answer":"C 10-1","isCorrect":true},{"answer":"C 10-2","isCorrect":false},{"answer":"C 10-3","isCorrect":false},{"answer":"C 10-4","isCorrect":false}]},{"questionText":"C 11","image":null,"answers":[{"answer":"C 11-1","isCorrect":true},{"answer":"C 11-2","isCorrect":false},{"answer":"C 11-3","isCorrect":false},{"answer":"C 11-4","isCorrect":false}]},{"questionText":"C 12","image":null,"answers":[{"answer":"C 12-1","isCorrect":true},{"answer":"C 12-2","isCorrect":false},{"answer":"C 12-3","isCorrect":false},{"answer":"C 12-4","isCorrect":false}]},{"questionText":"C 13","image":null,"answers":[{"answer":"C 13-1","isCorrect":true},{"answer":"C 13-2","isCorrect":false},{"answer":"C 13-3","isCorrect":false},{"answer":"C 13-4","isCorrect":false}]},{"questionText":"C 14","image":null,"answers":[{"answer":"C 14-1","isCorrect":true},{"answer":"C 14-2","isCorrect":false},{"answer":"C 14-3","isCorrect":false},{"answer":"C 14-4","isCorrect":false}]},{"questionText":"C 15","image":null,"answers":[{"answer":"C 15-1","isCorrect":true},{"answer":"C 15-2","isCorrect":false},{"answer":"C 15-3","isCorrect":false},{"answer":"C 15-4","isCorrect":false}]}]},"locale":"nb-no"}',
            'license' => 'BY-NC',
        ]);

        $url = "http://localhost/game/$game->id";
        $response = $this->withSession(['locale' => 'se-fi', 'userId' => $this->faker->uuid])
            ->post($url, $this->getSignedLtiParams($url, [
                'lti_version' => 'LTI-1p0',
                'lti_message_type' => 'basic-lti-launch-request',
            ]))
            ->assertOk()
            ->getOriginalContent();

        $this->assertInstanceOf(View::class, $response);
        $data = $response->getData();
        $this->assertSame($game->title, $data['title']);

        $gameSettings = json_decode($data['gameSettings'], flags: JSON_THROW_ON_ERROR);
        $this->assertCount(15, $gameSettings->questionSet->questions);
        $this->assertNull($gameSettings->questionSet->questions[0]->image);
        $this->assertCount(4, $gameSettings->questionSet->questions[0]->answers);
        $this->assertNull($gameSettings->questionSet->questions[0]->answers[0]->image);
    }
}
