<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItems;

interface ContentItemsSerializerInterface
{
    /**
     * @return array<mixed>
     */
    public function serialize(ContentItems $items): array;
}
