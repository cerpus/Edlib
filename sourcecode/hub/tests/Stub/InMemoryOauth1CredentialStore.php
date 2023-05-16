<?php

declare(strict_types=1);

namespace Tests\Stub;

use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1CredentialStoreInterface;

class InMemoryOauth1CredentialStore implements Oauth1CredentialStoreInterface
{
    /**
     * @var array<string, Oauth1Credentials>
     */
    private array $keyCredentialsMap = [];

    public function findByKey(string $key): Oauth1Credentials|null
    {
        return $this->keyCredentialsMap[$key] ?? null;
    }

    public function add(Oauth1Credentials $credentials): void
    {
        $this->keyCredentialsMap[$credentials->key] = $credentials;
    }
}
