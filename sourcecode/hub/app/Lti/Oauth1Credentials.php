<?php

namespace App\Lti;

use SensitiveParameter;

final readonly class Oauth1Credentials
{
    public string $consumerSecret;

    public function __construct(
        public string $consumerKey,
        #[SensitiveParameter] string $consumerSecret,
    ) {
        $this->consumerSecret = $consumerSecret;
    }
}
