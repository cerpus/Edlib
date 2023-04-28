<?php

declare(strict_types=1);

namespace App\Lti\ContentItem;

/**
 * @see https://www.imsglobal.org/lti/model/mediatype/application/vnd/ims/lti/v1/contentitems%2Bjson/index.html#LtiLinkItem
 */
class LtiLinkItem
{
    public function __construct(
        private readonly string $mediaType,
        private readonly ContentItemPlacement|null $contentItemPlacement = null,
        private readonly Image|null $icon = null,
        private readonly Image|null $thumbnail = null,
        private readonly string|null $text = null,
        private readonly string|null $title = null,
        private readonly string|null $url = null,
    ) {
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getContentItemPlacement(): ContentItemPlacement|null
    {
        return $this->contentItemPlacement;
    }

    public function getIcon(): Image|null
    {
        return $this->icon;
    }

    public function getText(): string|null
    {
        return $this->text;
    }

    public function getThumbnail(): Image|null
    {
        return $this->thumbnail;
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function getUrl(): string|null
    {
        return $this->url;
    }
}
