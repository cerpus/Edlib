<?php

namespace Tests\Traits;

use Cerpus\EdlibResourceKit\Resource\ResourceManagerInterface;
use Cerpus\EdlibResourceKit\ResourceKit;

trait MockMQ
{
    public function setUpMockMQ()
    {
        $manager = $this->createPartialMock(ResourceManagerInterface::class, ['save']);
        app()->instance(ResourceManagerInterface::class, $manager);

        $resourceKit = $this->createMock(ResourceKit::class);
        $resourceKit->method('getResourceManager')->willReturn($manager);
        app()->instance(ResourceKit::class, $resourceKit);
    }
}
