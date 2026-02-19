<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Edlib\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializer;
use App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking\LtiLinkItemSerializerInterface;
use App\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;

final readonly class EdlibLtiLinkItemSerializer implements LtiLinkItemSerializerInterface
{
    public function __construct(
        private LtiLinkItemSerializerInterface $serializer = new LtiLinkItemSerializer(),
    ) {
    }

    public function serialize(LtiLinkItem $item): array
    {
        $serialized = $this->serializer->serialize($item);

        if ($item instanceof EdlibLtiLinkItem) {
            if ($item->getEdlibVersionId() !== null) {
                $serialized['edlibVersionId'] = $item->getEdlibVersionId();
            }

            if ($item->getLicense() !== null) {
                $serialized['license'] = $item->getLicense();
            }

            if ($item->getLanguageIso639_3() !== null) {
                $serialized['languageIso639_3'] = $item->getLanguageIso639_3();
            }

            if ($item->isPublished() !== null) {
                $serialized['published'] = $item->isPublished();
            }

            if ($item->isShared() !== null) {
                $serialized['shared'] = $item->isShared();
            }

            if (count($item->getTags()) > 1) {
                $serialized['tag'] = $item->getTags();
            } elseif (count($item->getTags()) === 1) {
                $serialized['tag'] = $item->getTags()[0];
            }

            if ($item->getOwnerEmail() !== null) {
                $serialized['ownerEmail'] = $item->getOwnerEmail();
            }

            if ($item->getContentType() !== null) {
                $serialized['contentType'] = $item->getContentType();
            }

            if ($item->getContentTypeName() !== null) {
                $serialized['contentTypeName'] = $item->getContentTypeName();
            }
        }

        return $serialized;
    }
}
