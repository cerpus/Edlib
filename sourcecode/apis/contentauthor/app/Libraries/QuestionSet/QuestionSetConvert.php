<?php

namespace App\Libraries\QuestionSet;


use App\H5PContent;
use App\Http\Controllers\API\Handler\ContentTypeHandler;
use App\Libraries\DataObjects\ResourceDataObject;
use App\Libraries\DataObjects\ResourceMetadataDataObject;
use App\Libraries\Games\GameHandler;
use App\Libraries\Games\Millionaire\Millionaire;
use Cerpus\CoreClient\DataObjects\Answer;
use Cerpus\CoreClient\DataObjects\MultiChoiceQuestion;
use Cerpus\CoreClient\DataObjects\Questionset as CoreClientQuestionset;
use App\QuestionSet as QuestionSetModel;
use App\Libraries\H5P\Packages\QuestionSet as H5PQuestionSet;

class QuestionSetConvert
{

    /**
     * @param $convertTo
     * @param QuestionSetModel $questionSet
     * @param array $metadata
     * @return array|null
     * @throws \Exception
     */
    public function convert($convertTo, QuestionSetModel $questionSet, ResourceMetadataDataObject $metadata)
    {
        switch ($convertTo){
            case H5PQuestionSet::$machineName:
                return $this->createH5PQuestionSet($questionSet, $metadata);
            case Millionaire::$machineName:
                return $this->createMillionaireGame($questionSet, $metadata);
            default:
                throw new \Exception("Presentation '$convertTo' is not currently supported'");
        }
    }


    public function createH5PQuestionSet(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData)
    {
        $h5pQuiz = CoreClientQuestionset::create([
            'title' => $questionSet->title,
            'license' => $metaData->license,
            'share' => $metaData->share,
            'authId' => $questionSet->owner,
        ]);

        $questionSet->questions->each(function($question) use ($h5pQuiz){
            $h5pQuestion = MultiChoiceQuestion::create([
                'text' => $question->question_text,
            ]);
            $answers = $question->answers->map(function ($answer){
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

        // Add the tags from the original QuestionSet to the new content
        /** @var H5PContent $newContent */
        if ($newContent = H5PContent::find($content['id'])) {
            $newContent->updateMetaTags($metaData->tags ?? []);
        }

        return [
            $content['id'],
            $content['title'],
            H5PQuestionSet::$machineName,
            route('h5p.edit', $content['id']),
            ResourceDataObject::H5P
        ];
    }

    public function createMillionaireGame(QuestionSetModel $questionSet, ResourceMetadataDataObject $metaData)
    {
        $millionaire = app(Millionaire::class);
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
            ResourceDataObject::GAME
        ];
    }
}
