<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItemPlacement;

interface ContentItemPlacementSerializerInterface
{
    /**
     * @return array<mixed>
     *     The compact form JSON-LD representation of the content item
     *     placement
     */
    public function serialize(ContentItemPlacement $placement): array;
}
