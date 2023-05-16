<?php

declare(strict_types=1);

namespace Tests\Unit\Lti\Oauth1;

use App\Lti\Exception\Oauth1ValidationException;
use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1Signer;
use App\Lti\Oauth1\Oauth1Validator;
use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;
use Tests\Stub\ClockStub;
use Tests\Stub\InMemoryOauth1CredentialStore;
use Tests\Stub\RandomEngineStub;

#[CoversClass(Oauth1Signer::class)]
#[CoversClass(Oauth1Validator::class)]
#[CoversClass(Oauth1Request::class)]
final class Oauth1ValidatorTest extends TestCase
{
    private InMemoryOauth1CredentialStore $credentials;

    private Oauth1Validator $validator;

    protected function setUp(): void
    {
        $this->credentials = new InMemoryOauth1CredentialStore();

        $this->validator = new Oauth1Validator(
            $this->credentials,
            new Oauth1Signer(
                new ClockStub(),
                new Randomizer(new RandomEngineStub()),
            ),
            new ArrayCachePool(),
            new ClockStub(),
        );
    }

    #[DoesNotPerformAssertions]
    public function testValidOauth1RequestPasses(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->validator->validate($request);
    }

    public function testMissingConsumerKeyIsRejected(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('No consumer key provided'),
        );

        $this->validator->validate($request);
    }

    public function testMustMatchKeyOfExistingConsumer(): void
    {
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Provided key does not correspond to any known consumer'),
        );

        $this->validator->validate($request);
    }

    public function testMissingNonceIsRejected(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_signature' => 'YMMkbRwzQgrchvOiwY7k4/4Pq1Y=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(new Oauth1ValidationException('No nonce provided'));

        $this->validator->validate($request);
    }

    public function testNoncesCannotBeReused(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->validator->validate($request);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Provided nonce has already been used'),
        );

        $this->validator->validate($request);
    }

    public function testSignatureIsRequired(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('No signature provided'),
        );

        $this->validator->validate($request);
    }

    public function testInvalidSignaturesAreRejected(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaa=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Provided signature does not match'),
        );

        $this->validator->validate($request);
    }

    public function testRequiresHmacSha1Signature(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'BIlPuLKBDvvgvXSz8ur0FVIETgY=',
            'oauth_signature_method' => 'HMAC-SHA2',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Signature method must be "HMAC-SHA1"'),
        );

        $this->validator->validate($request);
    }

    public function testTimestampIsRequired(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'wpJ918w6kKO/gJ4a6SwXmBOZ4jE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('No timestamp provided'),
        );

        $this->validator->validate($request);
    }

    public function testTimestampPastAllowedLeewayIsRejected(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'OY9dl0+fRKdqwsx04JVhKU9b3rE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000301',
            'oauth_version' => '1.0',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Provided time deviates too much from server time'),
        );

        $this->validator->validate($request);
    }

    public function testVersionMustBeOnePointZero(): void
    {
        $this->credentials->add(new Oauth1Credentials('my-client', 'my-secret'));

        $request = new Oauth1Request('POST', 'https://example.com/', [
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'vGxugGVDGSOwpDxmkWXbuCi+EwI=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.1',
        ]);

        $this->expectExceptionObject(
            new Oauth1ValidationException('Provided version must be "1.0" or omitted'),
        );

        $this->validator->validate($request);
    }
}
