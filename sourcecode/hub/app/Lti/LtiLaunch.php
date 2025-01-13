<?php

declare(strict_types=1);

namespace App\Lti;

use Cerpus\EdlibResourceKit\Oauth1\Request;

class LtiLaunch
{
    public function __construct(
        private readonly Request $request,
        private readonly int|null $width = null,
        private readonly int|null $height = null,
    ) {}

    public function getRequest(): Request
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
