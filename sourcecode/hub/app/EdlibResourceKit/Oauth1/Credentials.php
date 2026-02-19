<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

use InvalidArgumentException;
use SensitiveParameter;

/**
 * Represents a key/secret pair for OAuth 1.
 *
 * Each {@link Credentials} object may also be used as a single-key credential
 * store.
 */
final readonly class Credentials implements CredentialStoreInterface
{
    public string $secret;

    public function __construct(
        public string $key,
        #[SensitiveParameter] string $secret,
    ) {
        if ($key === '') {
            throw new InvalidArgumentException('Key cannot be empty');
        }

        if ($secret === '') {
            throw new InvalidArgumentException('Secret cannot be empty');
        }

        $this->secret = $secret;
    }

    public function findByKey(string $key): Credentials|null
    {
        if ($this->key !== $key) {
            return null;
        }

        return $this;
    }
}
