<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItemPlacement;

final readonly class PlacementAdviceMapper implements PlacementAdviceMapperInterface
{
    public function map(array $data): ContentItemPlacement|null
    {
        return new ContentItemPlacement(
            Prop::getDisplayWidth($data),
            Prop::getDisplayHeight($data),
            Prop::getPresentationDocumentTarget($data),
            Prop::getWindowTarget($data),
        );
    }
}
