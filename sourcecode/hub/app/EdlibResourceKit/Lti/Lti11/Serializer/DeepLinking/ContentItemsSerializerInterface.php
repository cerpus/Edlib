<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;

interface ContentItemsSerializerInterface
{
    /**
     * @param array<ContentItem> $items
     * @return array<mixed>
     *     The compact form JSON-LD representation of the LTI content items
     */
    public function serialize(array $items): array;
}
