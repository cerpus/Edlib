<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItems;

final readonly class ContentItemsSerializer implements ContentItemsSerializerInterface
{
    public function __construct(
        private LtiLinkItemSerializerInterface $ltiLinkItemSerializer = new LtiLinkItemSerializer(),
    ) {
    }

    public function serialize(ContentItems $items): array
    {
        $items = iterator_to_array($items);

        $data = [
            '@context' => ContentItems::CONTEXT,
            '@graph' => array_map(
                $this->ltiLinkItemSerializer->serialize(...),
                $items,
            ),
        ];

        return $data;
    }
}
