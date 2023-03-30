<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1Signer;
use App\Lti\Oauth1\Oauth1Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;
use Tests\Stub\ClockStub;
use Tests\Stub\RandomEngineStub;

#[CoversClass(Oauth1Validator::class)]
#[CoversClass(Oauth1Request::class)]
final class Oauth1ValidatorTest extends TestCase
{
    private Oauth1Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Oauth1Validator(
            new Oauth1Signer(
                new ClockStub(),
                new Randomizer(new RandomEngineStub()),
            ),
        );
    }

    public function testValidOauth1RequestPasses(): void
    {
        $credentials = new Oauth1Credentials('my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->assertTrue($this->validator->validate($request, $credentials));
    }

    public function testInvalidSignatureIsRejected(): void
    {
        $credentials = new Oauth1Credentials('my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'signature-invalidating-value',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->assertFalse($this->validator->validate($request, $credentials));
    }

    public function testDoesNotValidateAgainstProvidedConsumerKey(): void
    {
        $credentials = new Oauth1Credentials('not-my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->assertFalse($this->validator->validate($request, $credentials));
    }

    public function testRequiresSignature(): void
    {
        $credentials = new Oauth1Credentials('not-my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->assertFalse($this->validator->validate($request, $credentials));
    }
}
