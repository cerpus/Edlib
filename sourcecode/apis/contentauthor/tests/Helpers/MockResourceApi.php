<?php

namespace Tests\Helpers;

use App\Apis\ResourceApiService;

trait MockResourceApi
{
    public function setUpResourceApi($returnCollaborators = [])
    {
        $resourceApiService = $this->getMockBuilder(ResourceApiService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resourceApiService->method("getCollaborators")->willReturn($returnCollaborators);

        app()->instance(ResourceApiService::class, $resourceApiService);
    }
}
