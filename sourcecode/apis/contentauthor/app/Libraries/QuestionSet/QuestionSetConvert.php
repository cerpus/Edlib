<?php

namespace App\Libraries\QuestionSet;

use App\Content;
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
    /**
     * @return array|null
     * @throws \Exception
     */
    public function convert($convertTo, QuestionSetModel $questionSet, ResourceMetadataDataObject $metadata)
    {
        switch ($convertTo) {
            case H5PQuestionSet::$machineName:
                return $this->createH5PQuestionSet($questionSet, $metadata);
            case Millionaire::$machineName:
                return $this->createMillionaireGame($questionSet, $metadata);
            default:
                throw new \Exception("Presentation '$convertTo' is not currently supported'");
        }
    }


    public function createH5PQuestionSet(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData): array
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
        /** @var ContentTypeHandler $contentTypeHandler */
        $contentTypeHandler = app(ContentTypeHandler::class);
        $content = $contentTypeHandler->storeQuestionset($h5pQuiz->toArray());

        return [
            $content['id'],
            $content['title'],
            H5PQuestionSet::$machineName,
            route('h5p.edit', $content['id']),
            Content::TYPE_H5P
        ];
    }

    public function createMillionaireGame(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData): array
    {
        /** @var Millionaire $millionaire */
        $millionaire = app(Millionaire::class);
        /** @var GameHandler $gameHandler */
        $gameHandler = app(GameHandler::class);
        $game = $gameHandler->store([
            'title' => $questionSet->title,
            'cards' => $questionSet,
            'license' => $metaData->license,
            'share' => $metaData->share,
            'authId' => $questionSet->owner,
            'tags' => $metaData->tags,
            'is_published' => $questionSet->is_published,
        ], $millionaire);

        return [
            $game['id'],
            $game['title'],
            "Game", //TODO: need to change this to what type of game when "the Core" gets back
            route('game.edit', $game['id']),
            Content::TYPE_GAME
        ];
    }
}
