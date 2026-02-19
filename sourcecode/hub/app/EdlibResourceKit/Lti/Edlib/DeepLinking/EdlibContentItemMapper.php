<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Edlib\DeepLinking;

use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemMapper;
use App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking\ContentItemMapperInterface;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use App\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;

final readonly class EdlibContentItemMapper implements ContentItemMapperInterface
{
    public function __construct(
        private ContentItemMapperInterface $mapper = new ContentItemMapper(),
    ) {
    }

    public function map(array $data): ContentItem
    {
        $item = $this->mapper->map($data);

        if ($item instanceof LtiLinkItem) {
            $item = (new EdlibLtiLinkItem(
                mediaType: $item->getMediaType(),
                icon: $item->getIcon(),
                placementAdvice: $item->getPlacementAdvice(),
                text: $item->getText(),
                thumbnail: $item->getThumbnail(),
                title: $item->getTitle(),
                url: $item->getUrl(),
                custom: $item->getCustom(),
                lineItem: $item->getLineItem(),
            ))
                ->withEdlibVersionId(Prop::getOfType($data, 'edlibVersionId', Prop::TYPE_NORMALIZED_STRING))
                ->withLanguageIso639_3(Prop::getOfType($data, 'languageIso639_3', Prop::TYPE_NORMALIZED_STRING))
                ->withLicense(Prop::getOfType($data, 'license', Prop::TYPE_NORMALIZED_STRING))
                ->withPublished(Prop::getOfType($data, 'published', Prop::TYPE_BOOLEAN))
                ->withShared(Prop::getOfType($data, 'shared', Prop::TYPE_BOOLEAN))
                ->withTags(Prop::getArrayOfType($data, 'tag', Prop::TYPE_NORMALIZED_STRING))
                ->withOwnerEmail(Prop::getOfType($data, 'ownerEmail', Prop::TYPE_NORMALIZED_STRING))
                ->withContentType(Prop::getOfType($data, 'contentType', Prop::TYPE_NORMALIZED_STRING))
                ->withContentTypeName(Prop::getOfType($data, 'contentTypeName', Prop::TYPE_NORMALIZED_STRING))
            ;
        }

        return $item;
    }
}
