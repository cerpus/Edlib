<?php

namespace App\Transformers;

use App\QuestionSetQuestion;
use Carbon\Carbon;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class QuestionSetsQuestionTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'created',
        'updated',
    ];

    protected array $availableIncludes = [
        'questionset',
        'answers',
    ];

    public function transform(QuestionSetQuestion $question): array
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

    private function getDate(QuestionSetQuestion $question, $field): Item
    {
        return $this->item(Carbon::parse($question->$field), new DateTransformer());
    }

    public function includeCreated(QuestionSetQuestion $question): Item
    {
        return $this->getDate($question, $question->getCreatedAtColumn());
    }

    public function includeUpdated(QuestionSetQuestion $question): Item
    {
        return $this->getDate($question, $question->getUpdatedAtColumn());
    }

    public function links(QuestionSetQuestion $question): array
    {
        return [
            'store' => route('questionsetquestion.store'),
            'self' => route('questionsetquestion.update', ['questionset' => $question->id]),
        ];
    }

    public function includeQuestionset(QuestionSetQuestion $question): Item
    {
        return $this->item($question->questionset, new QuestionSetsTransformer());
    }

    public function includeAnswers(QuestionSetQuestion $question): \League\Fractal\Resource\Collection
    {
        return $this->collection($question->answers, new QuestionSetsQuestionAnswerTransformer());
    }
}
