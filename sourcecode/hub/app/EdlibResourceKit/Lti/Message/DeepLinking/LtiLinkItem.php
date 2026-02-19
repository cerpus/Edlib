<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class LtiLinkItem extends ContentItem
{
    /**
     * @param array<mixed> $custom
     */
    public function __construct(
        string $mediaType = 'application/vnd.ims.lti.v1.ltilink',
        Image|null $icon = null,
        ContentItemPlacement|null $placementAdvice = null,
        string|null $text = null,
        Image|null $thumbnail = null,
        string|null $title = null,
        string|null $url = null,
        private readonly array $custom = [],
        private readonly LineItem|null $lineItem = null,
    ) {
        parent::__construct(
            $mediaType,
            $icon,
            $placementAdvice,
            $text,
            $thumbnail,
            $title,
            $url,
        );
    }

    public function getCustom(): array
    {
        return $this->custom;
    }

    public function getLineItem(): LineItem|null
    {
        return $this->lineItem;
    }
}
