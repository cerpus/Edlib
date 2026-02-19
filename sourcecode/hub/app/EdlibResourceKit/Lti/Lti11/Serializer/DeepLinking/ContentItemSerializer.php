<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;

final readonly class ContentItemSerializer implements ContentItemSerializerInterface
{
    public function __construct(
        private ContentItemPlacementSerializerInterface $contentItemPlacementSerializer = new ContentItemPlacementSerializer(),
        private ImageSerializerInterface $imageSerializer = new ImageSerializer(),
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function serialize(ContentItem $item): array
    {
        $serialized = [
            '@type' => 'ContentItem',
            Prop::MEDIA_TYPE => $item->getMediaType(),
        ];

        if ($item->getPlacementAdvice() !== null) {
            $serialized[Prop::PLACEMENT_ADVICE] = $this
                ->contentItemPlacementSerializer
                ->serialize($item->getPlacementAdvice());
        }

        if ($item->getIcon() !== null) {
            $serialized[Prop::ICON] = $this
                ->imageSerializer
                ->serialize($item->getIcon());
        }

        if ($item->getText() !== null) {
            $serialized[Prop::TEXT] = $item->getText();
        }

        if ($item->getThumbnail() !== null) {
            $serialized[Prop::THUMBNAIL] = $this
                ->imageSerializer
                ->serialize($item->getThumbnail());
        }

        if ($item->getTitle() !== null) {
            $serialized[Prop::TITLE] = $item->getTitle();
        }

        if ($item->getUrl() !== null) {
            $serialized[Prop::URL] = $item->getUrl();
        }

        return $serialized;
    }
}
