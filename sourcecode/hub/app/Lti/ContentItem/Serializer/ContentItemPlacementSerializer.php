<?php

declare(strict_types=1);

namespace App\Lti\ContentItem\Serializer;

use App\Lti\ContentItem\ContentItemPlacement;
use App\Lti\ContentItem\ContentItems;

final class ContentItemPlacementSerializer implements ContentItemPlacementSerializerInterface
{
    public function serialize(ContentItemPlacement $placement): array
    {
        $serialized = [];

        if ($placement->getDisplayWidth() !== null) {
            $serialized[ContentItems::PROP_DISPLAY_WIDTH] = $placement->getDisplayWidth();
        }

        if ($placement->getDisplayHeight() !== null) {
            $serialized[ContentItems::PROP_DISPLAY_HEIGHT] = $placement->getDisplayHeight();
        }

        if ($placement->getPresentationDocumentTarget() !== null) {
            $serialized[ContentItems::PROP_PRESENTATION_DOCUMENT_TARGET]
                = $placement->getPresentationDocumentTarget()->toShortName();
        }

        if ($placement->getWindowTarget() !== null) {
            $serialized[ContentItems::PROP_WINDOW_TARGET] = $placement->getWindowTarget();
        }

        return $serialized;
    }
}
