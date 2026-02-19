<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;

interface ContentItemSerializerInterface
{
    /**
     * @return array<mixed>
     *     The compact form JSON-LD representation of the LTI content item
     */
    public function serialize(ContentItem $item): array;
}
