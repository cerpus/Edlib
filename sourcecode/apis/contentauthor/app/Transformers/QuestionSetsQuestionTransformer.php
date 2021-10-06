<?php

namespace App\Transformers;

use App\QuestionSetQuestion;
use Carbon\Carbon;
use Cerpus\ImageServiceClient\Exceptions\FileNotFoundException;
use League\Fractal\TransformerAbstract;

class QuestionSetsQuestionTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'created',
        'updated',
    ];

    protected $availableIncludes = [
        'questionset',
        'answers'
    ];

    public function transform(QuestionSetQuestion $question)
    {
        return [
            'id' => $question->id,
            'externalReference' => $question->external_reference,
            'text' => $question->question_text,
            'imageObject' => $question->image,
            'imageUrl' => \ImageService::getHostingUrl($question->image),
            'order' => $question->order,
            //'links' => $this->links($question)
        ];
    }

    private function getDate(QuestionSetQuestion $question, $field)
    {
        return $this->item(Carbon::parse($question->$field), new DateTransformer);
    }

    public function includeCreated(QuestionSetQuestion $question)
    {
        return $this->getDate($question, $question->getCreatedAtColumn());
    }

    public function includeUpdated(QuestionSetQuestion $question)
    {
        return $this->getDate($question, $question->getUpdatedAtColumn());
    }

    public function links(QuestionSetQuestion $question)
    {
        return [
            'store' => route('questionsetquestion.store'),
            'self' => route('questionsetquestion.update', ['questionset' => $question->id]),
        ];
    }

    public function includeQuestionset(QuestionSetQuestion $question)
    {
        return $this->item($question->questionset, new QuestionSetsTransformer);
    }

    public function includeAnswers(QuestionSetQuestion $question)
    {
        return $this->collection($question->answers, new QuestionSetsQuestionAnswerTransformer);
    }
}