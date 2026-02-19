<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13;

use App\EdlibResourceKit\Lti13\Attribute\Claim;

/**
 * @see http://www.imsglobal.org/spec/lti/v1p3/#launch-presentation-claim
 */
class LaunchPresentation
{
    public function __construct(
        #[Claim] private readonly DocumentTarget|null $documentTarget = null,
        #[Claim] private readonly int|null $width = null,
        #[Claim] private readonly int|null $height = null,
        #[Claim] private readonly string|null $returnUrl = null,
    ) {
    }

    public function getDocumentTarget(): DocumentTarget|null
    {
        return $this->documentTarget;
    }

    public function getWidth(): int|null
    {
        return $this->width;
    }

    public function getHeight(): int|null
    {
        return $this->height;
    }

    public function getReturnUrl(): string|null
    {
        return $this->returnUrl;
    }
}
