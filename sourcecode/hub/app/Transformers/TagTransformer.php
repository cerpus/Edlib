<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\Tag;
use League\Fractal\TransformerAbstract;

final class TagTransformer extends TransformerAbstract
{
    /**
     * @return array<string, string>
     */
    public function transform(Tag $tag): array
    {
        $data = [
            'prefix' => $tag->prefix,
            'name' => $tag->name,
        ];

        if (isset($tag->pivot->verbatim_name)) {
            $data['verbatim_name'] = $tag->pivot->verbatim_name;
        }

        return $data;
    }
}
