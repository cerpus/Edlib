<?php

namespace App\Transformers;


use Embed\Adapters\Adapter;
use League\Fractal\TransformerAbstract;

class LinkMetadataTransformer extends TransformerAbstract
{
    public function transform(Adapter $embed): array
    {
        return [
            'title' => $embed->getTitle(),
            'image' => $embed->getImage(),
            'tags' => $embed->getTags(),
            'description' => $embed->getDescription(),
            'images' => $embed->getImages(),
            'type' => $embed->getType(),
            'url' => $embed->getUrl(),
            'code' => $embed->getCode(),
            'providerName' => $embed->getProviderName(),
            'providerUrl' => $embed->getProviderUrl(),
        ];
    }

}
