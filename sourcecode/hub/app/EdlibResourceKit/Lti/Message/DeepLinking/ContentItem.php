<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class ContentItem
{
    public function __construct(
        private readonly string $mediaType,
        private readonly Image|null $icon = null,
        private readonly ContentItemPlacement|null $placementAdvice = null,
        private readonly string|null $text = null,
        private readonly Image|null $thumbnail = null,
        private readonly string|null $title = null,
        private readonly string|null $url = null,
    ) {
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getIcon(): Image|null
    {
        return $this->icon;
    }

    public function getPlacementAdvice(): ContentItemPlacement|null
    {
        return $this->placementAdvice;
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
