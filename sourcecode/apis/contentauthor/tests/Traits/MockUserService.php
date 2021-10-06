<?php

namespace Tests\Traits;

use App\Http\Libraries\UserService;
use Illuminate\Support\Facades\Cache;

trait MockUserService
{

    public function setupUserService(array $methods)
    {
        /** @var \PHPUnit_Framework_MockObject_Builder_InvocationMocker $userService */
        $userService = $this->createPartialMock(UserService::class, array_keys($methods));
        foreach ($methods as $method => $returnValue) {
            if ($returnValue instanceof \Closure) {
                $userService->method($method)->willReturnCallback($returnValue);
                continue;
            }
            $userService->method($method)->willReturn($returnValue);
        }

        app()->instance(UserService::class, $userService);
    }
}