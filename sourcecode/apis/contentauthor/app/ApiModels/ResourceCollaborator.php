<?php

namespace App\ApiModels;

use App;

class ResourceCollaborator
{
    private $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
