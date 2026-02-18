<?php

namespace Tests\Integration\Libraries;

use App\Events\GameWasSaved;
use App\Game;
use App\Gametype;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\QuestionSet\QuestionSetConvert;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QuestionSetConverterTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testCreateMillionaireGameFromQuestionSet(): void
    {
        Event::fake();

        $questionSet = QuestionSet::factory()->create();
        /** @var QuestionSetQuestion $question */
        $question = $questionSet->questions()->save(QuestionSetQuestion::factory()->make());
        /** @var QuestionSetQuestionAnswer $a1 */
        $a1 = $question->answers()->save(QuestionSetQuestionAnswer::factory()->make([
            'answer_text' => 'A1',
            'correct' => true,
            'order' => 1,
        ]));
        /** @var QuestionSetQuestionAnswer $a2 */
        $a2 = $question->answers()->save(QuestionSetQuestionAnswer::factory()->make([
            'answer_text' => 'A2',
            'correct' => false,
            'order' => 2,
        ]));

        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);
        $resourceMetaObject = new ResourceMetadataDataObject(
            license: License::LICENSE_BY_NC,
            tags: ['List', 'of', 'tags'],
        );

        $questionsetConverter = app(QuestionSetConvert::class);
        /** @var Game $game */
        $game = $questionsetConverter->convert(
            Millionaire::$machineName,
            $questionSet,
            $resourceMetaObject,
        );

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'title' => $questionSet->title,
            'license' => License::LICENSE_BY_NC,
            'gametype' => $gameType->id,
            'owner' => $questionSet->owner,
        ]);

        $convertedCard = $game->game_settings->questionSet->questions[0];

        $this->assertSame($question->question_text, $convertedCard->questionText);
        $this->assertSame($a1->answer_text, $convertedCard->answers[0]->answer);
        $this->assertSame($a1->correct, $convertedCard->answers[0]->isCorrect);
        $this->assertSame($a2->answer_text, $convertedCard->answers[1]->answer);
        $this->assertSame($a2->correct, $convertedCard->answers[1]->isCorrect);
        Event::assertDispatched(GameWasSaved::class);
    }

    public function testCreateMillionaireGameFromArray(): void
    {
        Event::fake();

        $questionSet = [
            'owner' => $this->faker->uuid,
            'title' => $this->faker->sentence,
            'external_reference' => null,
            'language_code' => $this->faker->languageCode,
            'license' => '',
            'cards' => [
                [
                    'question' => [
                        'text' => 'QT',
                    ],
                    'answers' => [
                        [
                            'answerText' => 'AT 1',
                            'isCorrect' => true,
                        ],
                        [
                            'answerText' => 'AT 2',
                            'isCorrect' => false,
                        ],
                    ],
                ],
            ],
        ];

        $gameType = Gametype::factory()->create([
            'name' => Millionaire::$machineName,
        ]);

        $resourceMetaObject = new ResourceMetadataDataObject(
            license: License::LICENSE_BY_NC,
            tags: ['List', 'of', 'tags'],
        );

        $questionsetConverter = app(QuestionSetConvert::class);
        /** @var Game $game */
        $game = $questionsetConverter->convert(
            Millionaire::$machineName,
            $questionSet,
            $resourceMetaObject,
        );

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'title' => $questionSet['title'],
            'license' => License::LICENSE_BY_NC,
            'gametype' => $gameType->id,
            'owner' => $questionSet['owner'],
        ]);

        $this->assertDatabaseMissing('question_sets', [
            'title' => $questionSet['title'],
        ]);

        $inputCard = $questionSet['cards'][0];
        $convertedCard = $game->game_settings->questionSet->questions[0];

        $this->assertSame($inputCard['question']['text'], $convertedCard->questionText);
        $this->assertSame($inputCard['answers'][0]['answerText'], $convertedCard->answers[0]->answer);
        $this->assertSame($inputCard['answers'][0]['isCorrect'], $convertedCard->answers[0]->isCorrect);
        $this->assertSame($inputCard['answers'][1]['answerText'], $convertedCard->answers[1]->answer);
        $this->assertSame($inputCard['answers'][1]['isCorrect'], $convertedCard->answers[1]->isCorrect);
        Event::assertDispatched(GameWasSaved::class);
    }
}
