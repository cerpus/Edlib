<?php

namespace Tests\Helpers;

use Cerpus\EdlibResourceKit\Resource\ResourceManagerInterface;
use Cerpus\EdlibResourceKit\ResourceKitInterface;

trait MockMQ
{
    public function setUpMockMQ(): void
    {
        $manager = $this->createPartialMock(ResourceManagerInterface::class, ['save']);
        app()->instance(ResourceManagerInterface::class, $manager);

        $resourceKit = $this->createMock(ResourceKitInterface::class);
        $resourceKit->method('getResourceManager')->willReturn($manager);
        app()->instance(ResourceKitInterface::class, $resourceKit);
    }
}
