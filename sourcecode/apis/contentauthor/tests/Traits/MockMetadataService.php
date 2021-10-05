<?php

namespace Tests\Traits;

use Cerpus\MetadataServiceClient\Contracts\MetadataServiceContract as MetadataService;

trait MockMetadataService
{

    public function setupMetadataService(array $methods)
    {
        /** @var MetadataService $metadataService */
        $metadataService = $this->getMockBuilder(MetadataService::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Some sane default method mocks.
        // setEntityId is chainable...
        $metadataService->expects($this->any())
            ->method('setEntityId')
            ->will($this->returnSelf());

        // setEntityType is chainable
        $metadataService->expects($this->any())
            ->method('setEntityType')
            ->will($this->returnSelf());

        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $metadataService->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $metadataService->method($method)->willReturn($returnValue);
        }

        app()->instance(MetadataService::class, $metadataService);
    }
}
