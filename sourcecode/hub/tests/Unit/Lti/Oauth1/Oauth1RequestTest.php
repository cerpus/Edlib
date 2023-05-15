<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Request;
use PHPUnit\Framework\TestCase;

final class Oauth1RequestTest extends TestCase
{
    public function testGeneratesBaseString(): void
    {
        $request = new Oauth1Request(
            'POST',
            'http://example.com/request?b5=%3D%253D&a3=a&c%40=&a2=r%20b',
            [
                'c2' => '',
                'a3' => '2 q',
                'oauth_consumer_key' => '9djdj82h48djs9d2',
                'oauth_token' => 'kkk9d7dh3k39sjv7',
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp' => '137131201',
                'oauth_nonce' => '7d8f3e4a',
                'oauth_signature' => 'bYT5CMsGcbgUdFHObYMEfcx6bsw=',
            ],
        );

        $this->assertSame(
            'POST&http%3A%2F%2Fexample.com%2Frequest&a2%3Dr%2520b%26a3%3D2%2520q' .
            '%26a3%3Da%26b5%3D%253D%25253D%26c%2540%3D%26c2%3D%26oauth_consumer_' .
            'key%3D9djdj82h48djs9d2%26oauth_nonce%3D7d8f3e4a%26oauth_signature_m' .
            'ethod%3DHMAC-SHA1%26oauth_timestamp%3D137131201%26oauth_token%3Dkkk' .
            '9d7dh3k39sjv7',
            $request->generateSignatureBaseString()
        );
    }
}
