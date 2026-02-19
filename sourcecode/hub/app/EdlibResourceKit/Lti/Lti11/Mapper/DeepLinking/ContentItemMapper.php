<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Lti11\Mapper\DeepLinking;

use App\EdlibResourceKit\Lti\Exception\MappingException;
use App\EdlibResourceKit\Lti\Lti11\Context\DeepLinkingProps as Prop;
use App\EdlibResourceKit\Lti\Message\DeepLinking\ContentItem;
use App\EdlibResourceKit\Lti\Message\DeepLinking\FileItem;
use App\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;

final readonly class ContentItemMapper implements ContentItemMapperInterface
{
    public function __construct(
        private ImageMapperInterface $imageMapper = new ImageMapper(),
        private PlacementAdviceMapperInterface $placementAdviceMapper = new PlacementAdviceMapper(),
        private LineItemMapperInterface $lineItemMapper = new LineItemMapper(),
    ) {
    }

    public function map(array $data): ContentItem
    {
        $type = $data['@type'] ?? null;

        $mediaType = Prop::getMediaType($data)
            ?? throw new MappingException('Missing media type');

        $placementAdvice = $this->placementAdviceMapper
            ->map($data[Prop::PLACEMENT_ADVICE] ?? []);

        $icon = $this->imageMapper->map($data[Prop::ICON] ?? []);
        $thumbnail = $this->imageMapper->map($data[Prop::THUMBNAIL] ?? []);
        $lineItem = $this->lineItemMapper->map($data[Prop::LINE_ITEM] ?? []);

        if ($type === 'LtiLinkItem') {
            return new LtiLinkItem(
                $mediaType,
                $icon,
                $placementAdvice,
                Prop::getText($data),
                $thumbnail,
                Prop::getTitle($data),
                Prop::getUrl($data),
                custom: [], // TODO
                lineItem: $lineItem,
            );
        }

        if ($type === 'FileItem') {
            return new FileItem(
                $mediaType,
                Prop::getCopyAdvice($data),
                Prop::getExpiresAt($data),
                $icon,
                $placementAdvice,
                Prop::getText($data),
                $thumbnail,
                Prop::getTitle($data),
                Prop::getUrl($data),
            );
        }

        if ($type === 'ContentItem') {
            return new ContentItem(
                $mediaType,
                $icon,
                $placementAdvice,
                Prop::getText($data),
                $thumbnail,
                Prop::getTitle($data),
                Prop::getUrl($data),
            );
        }

        throw new MappingException('Unknown LTI content item type');
    }
}
