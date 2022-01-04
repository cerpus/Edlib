<?php

namespace App\ApiModels;

class ResourceLaunchInfo
{
    public function __construct(
        public string $url,
        public array $params,
    )
    {
    }
}
