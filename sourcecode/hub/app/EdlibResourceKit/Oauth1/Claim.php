<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

final readonly class Claim
{
    public const CONSUMER_KEY = 'oauth_consumer_key';
    public const NONCE = 'oauth_nonce';
    public const SIGNATURE = 'oauth_signature';
    public const SIGNATURE_METHOD = 'oauth_signature_method';
    public const TIMESTAMP = 'oauth_timestamp';
    public const TOKEN = 'oauth_token';
    public const VERSION = 'oauth_version';

    private function __construct()
    {
    }
}
