<?php

declare(strict_types=1);

namespace App\Lti\Serializer;

use App\Lti\LtiContent;
use Cerpus\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializerInterface;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;

final readonly class LtiContentSerializer implements LtiLinkItemSerializerInterface
{
    public function __construct(private LtiLinkItemSerializerInterface $serializer)
    {
    }

    public function serialize(LtiLinkItem $item): array
    {
        $serialized = $this->serializer->serialize($item);

        if (!$item instanceof LtiContent) {
            return $serialized;
        }

        if ($item->getLicense() !== null) {
            $serialized['license'] = $item->getLicense();
        }

        if ($item->getLanguageIso639_3() !== null) {
            $serialized['languageIso639_3'] = $item->getLanguageIso639_3();
        }

        return $serialized;
    }
}
