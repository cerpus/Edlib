<?php

declare(strict_types=1);

namespace App\Lti;

use App\Lti\Oauth1\Oauth1Request;

class LtiLaunch
{
    public function __construct(
        private readonly Oauth1Request $request,
        private readonly int|null $width = null,
        private readonly int|null $height = null,
    ) {
    }

    public function getRequest(): Oauth1Request
    {
        return $this->request;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}
