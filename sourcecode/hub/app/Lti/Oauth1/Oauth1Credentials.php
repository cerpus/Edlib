<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use InvalidArgumentException;
use SensitiveParameter;

final readonly class Oauth1Credentials
{
    public string $consumerSecret;

    public function __construct(
        public string $consumerKey,
        #[SensitiveParameter] string $consumerSecret,
    ) {
        if ($consumerKey === '') {
            throw new InvalidArgumentException('Consumer key cannot be empty');
        }

        if ($consumerSecret === '') {
            throw new InvalidArgumentException('Consumer secret cannot be empty');
        }

        $this->consumerSecret = $consumerSecret;
    }
}
