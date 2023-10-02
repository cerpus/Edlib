<?php

declare(strict_types=1);

namespace App\Oauth1;

use App\Models\LtiPlatform;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;

final readonly class LtiPlatformCredentialStore implements CredentialStoreInterface
{
    public function findByKey(string $key): Credentials|null
    {
        return LtiPlatform::where('key', $key)->first()?->getOauth1Credentials();
    }
}
