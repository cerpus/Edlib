<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

class Image
{
    public function __construct(
        private readonly string $uri,
        private readonly int|null $width = null,
        private readonly int|null $height = null,
    ) {
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHeight(): int|null
    {
        return $this->height;
    }

    public function getWidth(): int|null
    {
        return $this->width;
    }
}
