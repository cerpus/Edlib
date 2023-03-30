<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

final class Oauth1Claims
{
    public const CONSUMER_KEY = 'oauth_consumer_key';
    public const NONCE = 'oauth_nonce';
    public const SIGNATURE = 'oauth_signature';
    public const SIGNATURE_METHOD = 'oauth_signature_method';
    public const TIMESTAMP = 'oauth_timestamp';
    public const VERSION = 'oauth_version';
}
