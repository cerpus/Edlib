<?php

namespace App\ApiModels;

use App;

class LtiUsage
{
    public function __construct(
        public string  $id,
        public ?string  $consumerId,
        public string  $resourceId,
        public ?string $resourceVersionId,
        public string  $createdAt,
    )
    {
    }
}
