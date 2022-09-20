<?php
/**
 * Created by PhpStorm.
 * User: tsivert
 * Date: 4/28/18
 * Time: 3:34 PM
 */

namespace App\Http\Controllers\API\Transformers;

use Cerpus\QuestionBankClient\DataObjects\QuestionDataObject;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class QuestionTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'answers',
    ];

    public function transform(QuestionDataObject $question): array
    {
        return [
            'id' => $question->id,
            'text' => $question->text,
            'imageObject' => $question->getImageAt(0),
            'imageUrl' => \ImageService::getHostingUrl($question->getImageAt(0)),
        ];
    }

    public function includeAnswers(QuestionDataObject $question): Collection
    {
        return $this->collection($question->getAnswers(), new AnswerTransformer());
    }
}
