<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

interface Oauth1CredentialStoreInterface
{
    public function findByKey(string $key): Oauth1Credentials|null;
}
