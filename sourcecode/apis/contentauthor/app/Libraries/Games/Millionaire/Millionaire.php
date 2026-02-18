<?php

namespace App\Libraries\Games\Millionaire;

use App\Game;
use App\Gametype;
use App\Http\Libraries\License;
use App\Libraries\DataObjects\EditorConfigObject;
use App\Libraries\DataObjects\QuestionSetStateDataObject;
use App\Libraries\DataObjects\ResourceInfoDataObject;
use App\Libraries\Games\GameBase;
use App\Lti\Lti;
use App\QuestionSet;
use App\QuestionSetQuestion;
use App\QuestionSetQuestionAnswer;
use App\SessionKeys;
use App\Transformers\QuestionSetsTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Ramsey\Uuid\Uuid;

use function config;

class Millionaire extends GameBase
{
    public static string $machineName = "CERPUS.MILLIONAIRE";

    protected int $maxScore = 15;

    public function __construct(private readonly Lti $lti) {}

    public function getGameType()
    {
        $gameType = Gametype::mostRecent(self::$machineName);

        return $gameType->id;
    }

    public function createGameSettings(array $parameters, bool $asObject = false): object|string
    {
        $questions = $parameters['cards'];
        if (is_object($questions) && get_class($questions) === QuestionSet::class) {
            $questionsAndAnswers = $this->createGameSettingsFromQuestionset($questions);
        } else {
            $questionsAndAnswers = $this->createGameSettingsFromArray($questions);
        }
        $gameSettings = (object) [
            'questionSet' => (object) ['questions' => $questionsAndAnswers->toArray()],
            'locale' => $parameters['language_code'] ?? 'nb-no',
        ];
        return $asObject !== true ? json_encode($gameSettings, flags: JSON_THROW_ON_ERROR) : $gameSettings;
    }

    private function createGameSettingsFromQuestionset(QuestionSet $questionSet): Collection
    {
        return $questionSet->questions->map(function (QuestionSetQuestion $questionSetQuestion) {
            return [
                'questionText' => $questionSetQuestion->question_text,
                'image' => $questionSetQuestion->image,
                'answers' => $questionSetQuestion
                    ->answers
                    ->map(function (QuestionSetQuestionAnswer $answer) {
                        return [
                            'answer' => $answer->answer_text,
                            'isCorrect' => (bool) $answer->correct,
                        ];
                    })
                    ->toArray(),
            ];
        });
    }

    private function createGameSettingsFromArray(array $questions): Collection
    {
        return collect($questions)
            ->map(function ($question) {
                return [
                    'questionText' => $question['question']['text'],
                    'image' => null,
                    'answers' => array_map(function ($answer) {
                        return [
                            'answer' => $answer['answerText'],
                            'isCorrect' => (bool) $answer['isCorrect'],
                        ];
                    }, $question['answers']),
                ];
            });
    }

    public function view(Game $game): View
    {
        $game->load('gameType');

        return view('games.millionaire.show', [
            'title' => $game->title,
            'gameSettings' => json_encode($this->alterGameSettings($game->game_settings)),
            'scripts' => $game->gameType->getScripts(),
            'linked' => $game->gameType->getLinks(),
            'css' => $game->gameType->getCss(),
            'basePath' => $game->gameType->getBasePath(),
            'language' => $game->language_code,
            'resourceType' => sprintf($game::RESOURCE_TYPE_CSS, $game->getContentType()),
        ]);
    }

    public function alterGameSettings($gameSettings)
    {
        $gameSettings->questionSet->questions = collect($gameSettings->questionSet->questions)
            ->map(function ($question) {
                $question->image = null;
                $question->questionText = html_entity_decode(strip_tags($question->questionText));
                $question->answers = collect($question->answers)
                    ->map(function ($answer) {
                        $answer->answer = html_entity_decode(strip_tags($answer->answer));
                        $answer->image = null;
                        return $answer;
                    })
                    ->shuffle()
                    ->all();
                return $question;
            });

        return $gameSettings;
    }

    public static function customValidation($dataToBeValidated)
    {
        $errors = collect($dataToBeValidated['cards'])
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

    public function create(Request $request): View
    {
        $ltiRequest = $this->lti->getRequest($request);

        $extQuestionSetData = Session::get(SessionKeys::EXT_QUESTION_SET);
        Session::forget(SessionKeys::EXT_QUESTION_SET);

        $editorSetup = EditorConfigObject::create([
            'canList' => true,
            'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            'editorLanguage' => Session::get('locale', config('app.fallback_locale')),
        ])->toJson();

        $state = QuestionSetStateDataObject::create([
            'links' => (object) [
                "store" => route('questionset.store'),
            ],
            'questionSetJsonData' => $extQuestionSetData,
            'license' => License::getDefaultLicense(),
            'isPublished' => $ltiRequest?->getPublished() ?? false,
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->input('redirectToken'),
            'route' => route('questionset.store'),
            '_method' => "POST",
            'numberOfDefaultQuestions' => 15,
            'numberOfDefaultAnswers' => 4,
            'canAddRemoveQuestion' => false,
            'canAddRemoveAnswer' => false,
            'lockedPresentation' => Millionaire::$machineName,
        ])->toJson();

        return view('games.create', [
            'emails' => '',
            'editorSetup' => $editorSetup,
            'state' => $state,
        ]);
    }

    public function edit(Game $game, Request $request): View
    {
        $ltiRequest = $this->lti->getRequest($request);

        $this->addIncludeParse('questions.answers');
        $gameData = $this->convertDataToQuestionSet($game);

        $editorSetup = EditorConfigObject::create(
            [
                'canList' => $game->canList($request),
                'useLicense' => config('feature.licensing') === true || config('feature.licensing') === '1',
            ],
        );
        $editorSetup->setContentProperties(ResourceInfoDataObject::create([
            'id' => $game->id,
            'createdAt' => $game->created_at->toIso8601String(),
            'ownerName' => null,
        ]));

        $state = QuestionSetStateDataObject::create([
            'id' => $game->id,
            'title' => $game->title,
            'license' => $game->license,
            'isShared' => $ltiRequest?->getShared() ?? false,
            'redirectToken' => $request->get('redirectToken'),
            'route' => route('game.update', ['game' => $game->id]),
            '_method' => "PUT",
            'questionset' => $gameData,
            'editmode' => true,
            'presentation' => $game->gametype()->first()->name,
            'canAddRemoveCard' => false,
            'canAddRemoveAnswer' => false,
            'lockedPresentation' => Millionaire::$machineName,
        ]);


        return view('games.edit', [
            'game' => $game,
            'editorSetup' => $editorSetup->toJson(),
            'state' => $state->toJson(),
            'emails' => $game->getCollaboratorEmails(),
        ]);
    }

    private function convertDataToQuestionSet(Game $game): array
    {
        $questionSet = new QuestionSet();
        $questionSet->title = $game->title;
        $questionSet->questions = collect($game->game_settings->questionSet->questions)
            ->map(function ($question, $index) {
                $questionSetQuestion = new QuestionSetQuestion();
                $questionSetQuestion->question_text = $question->questionText;
                $questionSetQuestion->id = Uuid::uuid4();
                $questionSetQuestion->order = $index;

                $questionSetQuestion->answers = collect($question->answers)
                    ->map(function ($answer) {
                        $questionSetAnswer = new QuestionSetQuestionAnswer();
                        $questionSetAnswer->correct = $answer->isCorrect;
                        $questionSetAnswer->answer_text = $answer->answer;
                        $questionSetAnswer->id = Uuid::uuid4();
                        return $questionSetAnswer;
                    });

                return $questionSetQuestion;
            });

        return $this->buildItem($questionSet, new QuestionSetsTransformer());
    }
}
