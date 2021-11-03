<?php

namespace App\ApiModels;

use App;

class ResourceCollaborator
{
    private $tenantId;

    public function __construct(
        $tenantId
    )
    {
        $this->tenantId = $tenantId;
    }

    /**
     * @return string
     */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }
}
