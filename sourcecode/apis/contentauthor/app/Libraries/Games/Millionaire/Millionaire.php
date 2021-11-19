<?php

namespace App\Libraries\Games\Millionaire;


use App\Game;
use App\Gametype;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\Games\GameBase;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use App\Transformers\QuestionSetsTransformer;
use Cerpus\ImageServiceClient\DataObjects\ImageParamsObject;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Session;

class Millionaire extends GameBase
{

    public static $machineName = "CERPUS.MILLIONAIRE";

    protected $maxScore = 15;

    // Find and return the most recent version of the millionaire game
    public function getGameType()
    {
        $gameType = Gametype::mostRecent(self::$machineName);

        return $gameType->id;
    }

    public function createGameSettings($parameters, $asObject = false)
    {
        $questions = $parameters['cards'];
        if (is_object($questions) && get_class($questions) === QuestionSet::class) {
            $questionsAndAnswers = $this->createGameSettingsFromQuestionset($questions);
        } else {
            $questionsAndAnswers = $this->createGameSettingsFromForm($questions);
        }
        $gameSettings = (object)[
            'questionSet' => (object)['questions' => $questionsAndAnswers->toArray()],
            'locale' => 'nb-no',
        ];
        return $asObject !== true ? json_encode($gameSettings) : $gameSettings;
    }

    private function createGameSettingsFromQuestionset(QuestionSet $questionSet)
    {
        return $questionSet->questions->map(function (QuestionSetQuestion $questionSetQuestion) {
            $question = [
                'questionText' => $questionSetQuestion->question_text,
                'image' => $questionSetQuestion->image,
                'answers' => $questionSetQuestion
                    ->answers
                    ->map(function (QuestionSetQuestionAnswer $answer) {
                        return [
                            'answer' => $answer->answer_text,
                            'isCorrect' => (bool)$answer->correct,
                        ];
                    })
                    ->toArray()
            ];
            return $question;
        });
    }

    private function createGameSettingsFromForm($questions)
    {
        return collect($questions)
            ->map(function ($question) {
                return [
                    'questionText' => $question['question']['text'],
                    'image' => $question['question']['image']['id'],
                    'answers' => array_map(function ($answer) {
                        return [
                            'answer' => $answer['answerText'],
                            'isCorrect' => (bool)$answer['isCorrect'],
                        ];
                    }, $question['answers'])
                ];
            });
    }

    public function view(Game $game, $context, $preview)
    {
        $game->load('gameType');

        return view('games.millionaire.show', [
            'title' => $game->title,
            'gameSettings' => json_encode($this->alterGameSettings($game->game_settings)),
            'scripts' => $game->gameType->getAssets('scripts'),
            'linked' => $game->gameType->getAssets('links'),
            'css' => $game->gameType->getAssets('css'),
            'basePath' => $game->gameType->getPublicFolder(),
            'context' => $context,
            'language' => $game->language_code,
            'inDraftState' => $game->inDraftState(),
            'preview' => $preview,
            'resourceType' => sprintf($game::RESOURCE_TYPE_CSS, $game->getContentType()),
        ]);
    }

    public function alterGameSettings($gameSettings)
    {
        $questions = collect($gameSettings->questionSet->questions);
        $images = $questions
            ->pluck('image')
            ->filter(function ($image) {
                return !empty($image) && !filter_var($image, FILTER_VALIDATE_URL);
            })
            ->flatMap(function ($image) {
                return [
                    $image => [
                        'params' => ImageParamsObject::create([
                            'maxWidth' => 425,
                            'maxHeight' => 290
                        ])
                    ]
                ];
            });
        $imageUrls = \ImageService::getHostingUrls($images->toArray());
        $questions->transform(function ($question) use ($imageUrls) {
            if (!empty($question->image) && array_key_exists($question->image, $imageUrls)) {
                $question->image = $imageUrls[$question->image];
            } else {
                $question->image = "";
            }
            $question->questionText = html_entity_decode(strip_tags($question->questionText));
            $question->answers = collect($question->answers)
                ->map(function ($answer) {
                    $answer->answer = html_entity_decode(strip_tags($answer->answer));
                    return $answer;
                })
                ->shuffle()
                ->all();
            return $question;
        });
        $gameSettings->questionSet->questions = $questions;

        return $gameSettings;
    }

    public static function customValidation($data)
    {
        $errors = collect($data['cards'])
            ->map(function ($card) {
                return collect($card['answers'])
                    ->filter(function ($answer) {
                        return $answer['isCorrect'];
                    });
            })
            ->filter(function ($answers) {
                return $answers->count() !== 1;
            })
            ->flatMap(function ($error, $index) {
                $humanReadableCardIndex = $index + 1;
                return ["cards.$index.answers" => "Incorrect number of alternatives on question # $humanReadableCardIndex"];
            });

        if ($errors->isEmpty()) {
            return true;
        }
        return $errors;
    }

    public function edit(Game $game, Request $request)
    {
        $jwtTokenInfo = $request->session()->get('jwtToken', null);
        $licenseLib = new License(config('license'), config('cerpus-auth.key'), config('cerpus-auth.secret'));

        $this->addIncludeParse('questions.answers');
        $gameData = $this->convertDataToQuestionSet($game);

        $ownerName = $game->getOwnerName($game->owner);

        $editorSetup = EditorConfigObject::create([
                'useDraft' => Game::isDraftLogicEnabled(),
                'canPublish' => $game->canPublish($request),
                'canList' => $game->canList($request),
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ]
        );
        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $game->id,
            'createdAt' => $game->created_at->toIso8601String(),
            'ownerName' => !empty($ownerName) ? $ownerName : null,
        ]));

        $state = QuestionSetStateDataObject::create([
            'id' => $game->id,
            'title' => $game->title,
            'license' => $licenseLib->getLicense($game->id),
            'isPublished' => !$game->inDraftState(),
            'share' => !$game->isPublished() ? 'private' : 'share',
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('game.update', ['game' => $game->id]),
            '_method' => "PUT",
            'questionset' => $gameData,
            'editmode' => true,
            'presentation' => $game->gametype()->first()->name,
        ]);


        return view('games.millionaire.edit', [
            'game' => $game,
            'editorSetup' => $editorSetup->toJson(),
            'state' => $state->toJson(),
            'emails' => $game->getCollaboratorEmails(),
            'jwtToken' => $jwtTokenInfo && isset($jwtTokenInfo['raw']) ? $jwtTokenInfo['raw'] : null,
        ]);
    }

    private function convertDataToQuestionSet($game)
    {
        $questionSet = QuestionSet::make();
        $questionSet->title = $game->title;
        $questionSet->questions = collect($game->game_settings->questionSet->questions)
            ->map(function ($question, $index) {
                $questionSetQuestion = QuestionSetQuestion::make();
                $questionSetQuestion->question_text = $question->questionText;
                $questionSetQuestion->image = $question->image;
                $questionSetQuestion->id = Uuid::uuid4();
                $questionSetQuestion->order = $index;

                $questionSetQuestion->answers = array_map(function ($answer) {
                    $questionSetAnswer = QuestionSetQuestionAnswer::make();
                    $questionSetAnswer->correct = $answer->isCorrect;
                    $questionSetAnswer->answer_text = $answer->answer;
                    $questionSetAnswer->id = Uuid::uuid4();
                    return $questionSetAnswer;
                }, $question->answers);

                return $questionSetQuestion;
            });
        return $this->buildItem($questionSet, new QuestionSetsTransformer);
    }
}
