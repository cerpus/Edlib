<?php

namespace App\Libraries\QuestionSet;

use App\Content;
use App\Game;
use App\H5PContent;
use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Libraries\DataObjects\Answer;
use App\Libraries\DataObjects\MultiChoiceQuestion;
use App\Libraries\DataObjects\Questionset as QuestionSetData;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\GameHandler;
use App\Libraries\Games\Millionaire\Millionaire;
use App\Libraries\H5P\Packages\QuestionSet as H5PQuestionSet;
use App\QuestionSet as QuestionSetModel;

class QuestionSetConvert
{
    public function __construct(
        private readonly ContentTypeHandler $contentTypeHandler,
        private readonly GameHandler $gameHandler,
    ) {
    }

    public function convert(string $convertTo, QuestionSetModel $questionSet, ResourceMetadataDataObject $metadata): Content
    {
        return match ($convertTo) {
            H5PQuestionSet::$machineName => $this->createH5PQuestionSet($questionSet, $metadata),
            Millionaire::$machineName => $this->createMillionaireGame($questionSet, $metadata),
            default => throw new \InvalidArgumentException("Presentation '$convertTo' is not currently supported'"),
        };
    }

    public function createH5PQuestionSet(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData): H5PContent
    {
        $h5pQuiz = QuestionSetData::create([
            'title' => $questionSet->title,
            'license' => $metaData->license,
            'share' => $metaData->share,
            'authId' => $questionSet->owner,
        ]);

        $questionSet->questions->each(function ($question) use ($h5pQuiz) {
            /** @var MultiChoiceQuestion $h5pQuestion */
            $h5pQuestion = MultiChoiceQuestion::create([
                'text' => $question->question_text,
            ]);
            $answers = $question->answers->map(function ($answer) {
                return Answer::create([
                    'text' => $answer->answer_text,
                    'correct' => $answer->correct
                ]);
            });
            $h5pQuestion->addAnswers($answers);
            $h5pQuiz->addQuestion($h5pQuestion);
        });

        return $this->contentTypeHandler->storeQuestionset($h5pQuiz->toArray());
    }

    public function createMillionaireGame(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData): Game
    {
        return $this->gameHandler->store([
            'title' => $questionSet->title,
            'cards' => $questionSet,
            'license' => $metaData->license,
            'share' => $metaData->share,
            'authId' => $questionSet->owner,
            'tags' => $metaData->tags,
            'is_published' => $questionSet->is_published,
        ], new Millionaire());
    }
}
