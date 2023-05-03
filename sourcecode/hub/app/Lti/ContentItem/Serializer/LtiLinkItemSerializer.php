<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItems;
use App\Lti\ContentItem\LtiLinkItem;

final readonly class LtiLinkItemSerializer implements LtiLinkItemSerializerInterface
{
    public function __construct(
        private ContentItemPlacementSerializerInterface $contentItemPlacementSerializer = new ContentItemPlacementSerializer(),
        private ImageSerializerInterface $imageSerializer = new ImageSerializer(),
    ) {
    }

    public function serialize(LtiLinkItem $linkItem): array
    {
        $serialized = [
            '@type' => 'LtiLinkItem',
            ContentItems::PROP_MEDIA_TYPE => $linkItem->getMediaType(),
        ];

        if ($linkItem->getContentItemPlacement() !== null) {
            $serialized[ContentItems::PROP_PLACEMENT_ADVICE] =
                $this->contentItemPlacementSerializer
                    ->serialize($linkItem->getContentItemPlacement());
        }

        if ($linkItem->getIcon() !== null) {
            $serialized[ContentItems::PROP_ICON] = $this
                ->imageSerializer
                ->serialize($linkItem->getIcon());
        }

        if ($linkItem->getText() !== null) {
            $serialized[ContentItems::PROP_TEXT] = $linkItem->getText();
        }

        if ($linkItem->getThumbnail() !== null) {
            $serialized[ContentItems::PROP_THUMBNAIL] = $this
                ->imageSerializer
                ->serialize($linkItem->getThumbnail());
        }

        if ($linkItem->getTitle() !== null) {
            $serialized[ContentItems::PROP_TITLE] = $linkItem->getTitle();
        }

        if ($linkItem->getUrl() !== null) {
            $serialized[ContentItems::PROP_URL] = $linkItem->getUrl();
        }

        return $serialized;
    }
}
