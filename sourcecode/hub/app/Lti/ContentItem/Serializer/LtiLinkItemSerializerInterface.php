<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\LtiLinkItem;

interface LtiLinkItemSerializerInterface
{
    /**
     * @return array<mixed>
     */
    public function serialize(LtiLinkItem $linkItem): array;
}
