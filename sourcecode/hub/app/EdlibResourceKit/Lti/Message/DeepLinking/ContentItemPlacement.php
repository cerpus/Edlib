<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class ContentItemPlacement
{
    public function __construct(
        private readonly int|null $displayWidth = null,
        private readonly int|null $displayHeight = null,
        private readonly PresentationDocumentTarget|null $presentationDocumentTarget = null,
        private readonly string|null $windowTarget = null,
    ) {
    }

    public function getDisplayWidth(): int|null
    {
        return $this->displayWidth;
    }

    public function getDisplayHeight(): int|null
    {
        return $this->displayHeight;
    }

    public function getPresentationDocumentTarget(): PresentationDocumentTarget|null
    {
        return $this->presentationDocumentTarget;
    }

    public function getWindowTarget(): string|null
    {
        return $this->windowTarget;
    }
}
