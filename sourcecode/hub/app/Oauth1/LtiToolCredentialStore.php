<?php

declare(strict_types=1);

namespace App\Oauth1;

use App\Models\LtiTool;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;

final readonly class LtiToolCredentialStore implements CredentialStoreInterface
{
    public function findByKey(string $key): Credentials|null
    {
        return LtiTool::where('consumer_key', $key)->first()?->getOauth1Credentials();
    }
}
