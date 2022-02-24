<?php

namespace Tests\Traits;

use Cerpus\VersionClient\VersionClient;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;

trait MockVersioningTrait
{
    public function setupVersion(array $methods = [])
    {
        /** @var MockObject|VersionClient $versionClient */
        $versionClient = $this->createPartialMock(VersionClient::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof Closure) {
                $versionClient->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $versionClient->method($method)->willReturn($returnValue);
        }

        app()->instance(VersionClient::class, $versionClient);
    }
}
