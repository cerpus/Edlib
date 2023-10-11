<?php

namespace Tests\Integration\Http\Controllers;

use App\ApiModels\User;
use App\Game;
use App\Gametype;
use App\Http\Controllers\GameController;
use App\Http\Libraries\License;
use App\Libraries\Games\Millionaire\Millionaire;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\Helpers\MockAuthApi;
use Tests\TestCase;

class GameControllerTest extends TestCase
{
    use RefreshDatabase;
    use MockAuthApi;
    use WithFaker;

    public function testEdit(): void
    {
        $user = new User($this->faker->uuid, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->setupAuthApi(['getUser' => $user]);
        $this->session([
            'authId' => $user->getId(),
        ]);
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $game = Game::factory()->create([
            'owner' => $user->getId(),
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
        $this->assertEquals('Emily Quackfaster', $editorSetup['contentProperties']['ownerName']);
    }

    public function testUpdate(): void
    {
        $user = new User($this->faker->uuid, 'Emily', 'Quackfaster', 'emily.quackfaster@duckburg.quack');
        $this->setupAuthApi(['getUser' => $user]);
        $this->session([
            'authId' => $user->getId(),
        ]);
        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $game = Game::factory()->create([
            'owner' => $user->getId(),
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
            'isPublished' => false,
            'license' => License::LICENSE_BY_NC,
            'questionSetJsonData' => '{"selectedPresentation":"'.Millionaire::$machineName.'"}',
            'cards' => json_decode('[{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]},{"question":{"text":"Updated question","image":{"id":""}},"answers":[{"answerText":"First answer","isCorrect":true,"image":null},{"answerText":"Next answer","isCorrect":false,"image":null},{"answerText":"Another answer","isCorrect":false,"image":null},{"answerText":"Last answer","isCorrect":false,"image":null}]}]', true),
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
}
