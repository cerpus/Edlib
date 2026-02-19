<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

interface CredentialStoreInterface
{
    public function findByKey(string $key): Credentials|null;
}
