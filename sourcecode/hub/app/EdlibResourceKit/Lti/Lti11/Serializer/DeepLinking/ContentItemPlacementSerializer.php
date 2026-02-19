<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Serializer\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItemPlacement;

final readonly class ContentItemPlacementSerializer implements ContentItemPlacementSerializerInterface
{
    public function serialize(ContentItemPlacement $placement): array
    {
        $serialized = [];

        if ($placement->getDisplayWidth() !== null) {
            $serialized[Prop::DISPLAY_WIDTH] = $placement->getDisplayWidth();
        }

        if ($placement->getDisplayHeight() !== null) {
            $serialized[Prop::DISPLAY_HEIGHT] = $placement->getDisplayHeight();
        }

        if ($placement->getPresentationDocumentTarget() !== null) {
            $serialized[Prop::PRESENTATION_DOCUMENT_TARGET] =
                $placement->getPresentationDocumentTarget()->value;
        }

        if ($placement->getWindowTarget() !== null) {
            $serialized[Prop::WINDOW_TARGET] = $placement->getWindowTarget();
        }

        return $serialized;
    }
}
