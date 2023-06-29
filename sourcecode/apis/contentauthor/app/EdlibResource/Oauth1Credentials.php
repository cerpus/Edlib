<?php

declare(strict_types=1);

namespace App\EdlibResource;

use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use SensitiveParameter;

final readonly class Oauth1Credentials implements CredentialStoreInterface
{
    public function __construct(
        private string $consumerKey,
        #[SensitiveParameter] private string $consumerSecret,
    ) {
    }

    public function findByKey(string $key): Credentials|null
    {
        if ($key === $this->consumerKey) {
            return new Credentials($this->consumerKey, $this->consumerSecret);
        }

        return null;
    }
}
