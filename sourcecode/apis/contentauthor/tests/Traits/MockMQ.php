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

        $manager = $this->createMock(ResourceKit::class);
        $manager->method('getResourceManager')->willReturn($manager);
    }
}
