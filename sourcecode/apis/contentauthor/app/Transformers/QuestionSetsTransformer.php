<?php

namespace App\Transformers;

use App\QuestionSet;
use Carbon\Carbon;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class QuestionSetsTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'created',
        'updated',
    ];

    protected array $availableIncludes = [
        'questions',
    ];

    public function transform(QuestionSet $questionSet): array
    {
        return [
            'id' => $questionSet->id,
            'external_reference' => $questionSet->external_reference,
            'title' => $questionSet->title,
            'links' => !empty($questionSet->id) ? $this->links($questionSet) : [],
            'langCode' => $questionSet->language_code,
            'tags' => (mb_strlen($questionSet->tags) > 0 ? explode(',', $questionSet->tags) : []),
            'license' => $questionSet->license,
        ];
    }

    private function getDate(QuestionSet $questionSet, $field): Item
    {
        return $this->item(Carbon::parse($questionSet->$field), new DateTransformer());
    }

    public function includeCreated(QuestionSet $set): Item
    {
        return $this->getDate($set, $set->getCreatedAtColumn());
    }

    public function includeUpdated(QuestionSet $set): Item
    {
        return $this->getDate($set, $set->getUpdatedAtColumn());
    }

    public function links(QuestionSet $questionSet): array
    {
        return [
            'store' => route('questionset.store'),
            'self' => route('questionset.update', ['questionset' => $questionSet->id]),
            'edit' => route('questionset.edit', ['questionset' => $questionSet->id]),
        ];
    }

    public function includeQuestions(QuestionSet $questionSet): Collection
    {
        return $this->collection($questionSet->questions, new QuestionSetsQuestionTransformer());
    }
}
