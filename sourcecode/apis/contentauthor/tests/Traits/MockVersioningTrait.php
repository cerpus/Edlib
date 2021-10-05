<?php

namespace Tests\Traits;


use Cerpus\VersionClient\VersionClient;

trait MockVersioningTrait
{

    public function setupVersion(array $methods = [])
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $versionClient */
        $versionClient = $this->createPartialMock(VersionClient::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $versionClient->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $versionClient->method($method)->willReturn($returnValue);
        }

        app()->instance(VersionClient::class, $versionClient);
    }
}
