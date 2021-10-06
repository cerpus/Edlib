<?php

namespace App\Transformers;


use App\QuestionSet;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class QuestionSetsTransformer extends TransformerAbstract
{

    protected $defaultIncludes = [
        'created',
        'updated',
    ];

    protected $availableIncludes = [
        'questions'
    ];

    public function transform(QuestionSet $questionSet)
    {
        return [
            'id' => $questionSet->id,
            'external_reference' => $questionSet->external_reference,
            'title' => $questionSet->title,
            'links' => !empty($questionSet->id) ? $this->links($questionSet) : [],
            'langCode' => $questionSet->language_code,
            'tags' => (mb_strlen($questionSet->tags) > 0 ? explode(',', $questionSet->tags) : []),
        ];
    }

    private function getDate(QuestionSet $questionSet, $field)
    {
        return $this->item(Carbon::parse($questionSet->$field), new DateTransformer);
    }

    public function includeCreated(QuestionSet $set)
    {
        return $this->getDate($set, $set->getCreatedAtColumn());
    }

    public function includeUpdated(QuestionSet $set)
    {
        return $this->getDate($set, $set->getUpdatedAtColumn());
    }

    public function links(QuestionSet $questionSet)
    {
        return [
            'store' => route('questionset.store'),
            'self' => route('questionset.update', ['questionset' => $questionSet->id]),
            'edit' => route('questionset.edit', ['questionset' => $questionSet->id]),
        ];
    }

    public function includeQuestions(QuestionSet $questionSet)
    {
        return $this->collection($questionSet->questions, new QuestionSetsQuestionTransformer);
    }
}
