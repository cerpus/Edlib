<?php

namespace App\Transformers;

use Embed\Extractor;
use League\Fractal\TransformerAbstract;

class LinkMetadataTransformer extends TransformerAbstract
{
    public function transform(Extractor $embed): array
    {
        return [
            'title' => $embed->title,
            'image' => (string) $embed->image,
            'tags' => $embed->keywords,
            'description' => $embed->description,
            'url' => (string) $embed->url,
            'code' => $embed->code?->html,
            'providerName' => $embed->providerName,
            'providerUrl' => (string) $embed->providerUrl,
        ];
    }
}
