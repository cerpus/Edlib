<?php

namespace Tests\Traits;

use App\Apis\AuthApiService;

trait MockAuthApi
{
    public function setupAuthApi(array $methods)
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $authApiService */
        $authApiService = $this->createPartialMock(AuthApiService::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $authApiService->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $authApiService->method($method)->willReturn($returnValue);
        }

        app()->instance(AuthApiService::class, $authApiService);
    }
}
