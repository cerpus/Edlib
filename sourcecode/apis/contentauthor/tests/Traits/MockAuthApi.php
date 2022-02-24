<?php

namespace Tests\Traits;

use App\Apis\AuthApiService;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;

trait MockAuthApi
{
    public function setupAuthApi(array $methods)
    {
        /** @var MockObject|AuthApiService $authApiService */
        $authApiService = $this->createPartialMock(AuthApiService::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof Closure) {
                $authApiService->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $authApiService->method($method)->willReturn($returnValue);
        }

        $this->instance(AuthApiService::class, $authApiService);
    }
}
