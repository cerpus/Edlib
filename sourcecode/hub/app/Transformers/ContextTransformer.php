<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\Context;
use League\Fractal\TransformerAbstract;

final class ContextTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(Context $context): array
    {
        return [
            'id' => $context->id,
            'name' => $context->name,
            'links' => [
                'self' => route('api.contexts.show', [$context]),
            ],
        ];
    }
}
