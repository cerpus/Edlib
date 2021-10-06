<?php

namespace App\Http\Controllers\API\Transformers;


use Cerpus\QuestionBankClient\DataObjects\AnswerDataObject;
use League\Fractal\TransformerAbstract;

class AnswerTransformer extends TransformerAbstract
{
    public function transform(AnswerDataObject $answer)
    {
        return [
            'id' => $answer->id,
            'text' => $answer->text,
            'isCorrect' => $answer->isCorrect,
            'questionId' => $answer->questionId,
            'imageObject' => $answer->getImageAt(0),
            'imageUrl' => \ImageService::getHostingUrl($answer->getImageAt(0)),
        ];
    }
}