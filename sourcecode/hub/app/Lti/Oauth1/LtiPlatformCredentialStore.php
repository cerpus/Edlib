<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Models\LtiPlatform;
use App\EdlibResourceKit\Oauth1\Credentials;
use App\EdlibResourceKit\Oauth1\CredentialStoreInterface;

final readonly class LtiPlatformCredentialStore implements CredentialStoreInterface
{
    public function findByKey(string $key): Credentials|null
    {
        return LtiPlatform::where('key', $key)->first()?->getOauth1Credentials();
    }
}
