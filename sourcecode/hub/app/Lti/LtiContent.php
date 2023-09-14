<?php

declare(strict_types=1);

namespace App\Lti;

use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\ContentItemPlacement;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\Image;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\LtiLinkItem;
use Cerpus\EdlibResourceKit\Lti\Message\DeepLinking\PresentationDocumentTarget;

class LtiContent extends LtiLinkItem
{
    public function __construct(
        string $title,
        string $url,
        Image|null $icon = null,
        Image|null $thumbnail = null,
        private readonly string|null $languageIso639_3 = null,
        private readonly string|null $license = null,
    ) {
        parent::__construct(
            icon: $icon,
            thumbnail: $thumbnail,
            placementAdvice: new ContentItemPlacement(
                presentationDocumentTarget: PresentationDocumentTarget::Iframe,
            ),
            title: $title,
            url: $url,
        );
    }

    public function getLanguageIso639_3(): string|null
    {
        return $this->languageIso639_3;
    }

    public function getLicense(): string|null
    {
        return $this->license;
    }
}
