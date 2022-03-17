<?php

namespace Tests\Traits;

use App\ApiModels\User;
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

    private function makeAuthUser(): User
    {
        return new User(
            id: '323',
            firstName: 'Donald',
            lastName: 'Duck',
            email: 'potus@whitehouse.gov',
        );
    }

    private function withAuthenticated(User $user): self
    {
        return $this->withSession([
            'authId' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getFirstName().' '.$user->getLastName(),
            'verifiedEmails' => [$user->getEmail()],
        ]);
    }
}
