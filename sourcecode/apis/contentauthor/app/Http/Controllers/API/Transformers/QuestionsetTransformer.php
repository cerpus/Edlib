<?php

namespace App\Http\Controllers\API\Transformers;

use Cerpus\QuestionBankClient\DataObjects\QuestionsetDataObject;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class QuestionsetTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'questions',
    ];

    public function transform(QuestionsetDataObject $questionset): array
    {
        return [
            'id' => $questionset->id,
            'title' => $questionset->title,
            'numberOfQuestions' => $questionset->questionCount,
            'links' => [
                'self' => route('api.get.questionset', ['questionsetId' => $questionset->id]),
            ],
        ];
    }

    public function includeQuestions(QuestionsetDataObject $questions): Collection
    {
        return $this->collection($questions->getQuestions(), new QuestionTransformer());
    }
}
