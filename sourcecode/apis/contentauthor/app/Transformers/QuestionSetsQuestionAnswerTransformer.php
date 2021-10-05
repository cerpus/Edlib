<?php

namespace App\Transformers;

use App\QuestionSetQuestionAnswer;
use Carbon\Carbon;
use Cerpus\ImageServiceClient\Exceptions\FileNotFoundException;
use League\Fractal\TransformerAbstract;

class QuestionSetsQuestionAnswerTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'created',
        'updated',
    ];

    protected $availableIncludes = [
        'question',
    ];

    public function transform(QuestionSetQuestionAnswer $answer)
    {
        return [
            'id' => $answer->id,
            'externalReference' => $answer->external_reference,
            'text' => $answer->answer_text,
            'imageObject' => $answer->image,
            'imageUrl' => \ImageService::getHostingUrl($answer->image),
            'order' => $answer->order,
            'correct' =>(bool)$answer->correct,
            //'links' => $this->links($answer)
        ];
    }

    private function getDate(QuestionSetQuestionAnswer $answer, $field)
    {
        return $this->item(Carbon::parse($answer->$field), new DateTransformer);
    }

    public function includeCreated(QuestionSetQuestionAnswer $answer)
    {
        return $this->getDate($answer, $answer->getCreatedAtColumn());
    }

    public function includeUpdated(QuestionSetQuestionAnswer $answer)
    {
        return $this->getDate($answer, $answer->getUpdatedAtColumn());
    }

    public function links(QuestionSetQuestionAnswer $answer)
    {
        return [
            'store' => route('questionsetquestionanswer.store'),
            'self' => route('questionsetquestionanswer.update', ['question' => $answer->id]),
        ];
    }

    public function includeQuestion(QuestionSetQuestionAnswer $answer)
    {
        return $this->item($answer->question(), new QuestionSetsTransformer);
    }
}