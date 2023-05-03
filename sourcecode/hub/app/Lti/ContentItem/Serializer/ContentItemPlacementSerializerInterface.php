<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItemPlacement;

interface ContentItemPlacementSerializerInterface
{
    /**
     * @return array<mixed>
     */
    public function serialize(ContentItemPlacement $placement): array;
}
