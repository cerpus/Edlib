<?php

namespace Tests\Unit\Lti;

use App\Lti\Oauth1Credentials;
use App\Lti\Oauth1Request;
use App\Lti\Oauth1Signer;
use App\Lti\Oauth1SignerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Random\Randomizer;
use Tests\Stub\ClockStub;
use Tests\Stub\RandomEngineStub;
use Tests\TestCase;

#[CoversClass(Oauth1SignerFactory::class)]
#[CoversClass(Oauth1Signer::class)]
#[CoversClass(Oauth1Credentials::class)]
#[CoversClass(Oauth1Request::class)]
final class Oauth1SignerTest extends TestCase
{
    private Oauth1SignerFactory $signerFactory;

    protected function setUp(): void
    {
        $this->signerFactory = new Oauth1SignerFactory(
            new ClockStub(),
            new Randomizer(new RandomEngineStub()),
        );
    }

    public function testSignsOauth1Requests(): void
    {
        $request = $this->signerFactory
            ->create(new Oauth1Credentials('my-client', 'my-secret'))
            ->sign('POST', 'https://example.com/');

        $this->assertEquals([
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => '6w+On/hrM4ijTwIQDyCylJv3sUE=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ], $request->toArray());
    }

    public function testSignsOauth1RequestsWithExtraParams(): void
    {
        $request = $this->signerFactory
            ->create(new Oauth1Credentials('my-client', 'my-secret'))
            ->sign('POST', 'https://example.com/', [
                'lti_version' => 'LTI-1p0',
            ]);

        $this->assertEquals([
            'lti_version' => 'LTI-1p0',
            'oauth_consumer_key' => 'my-client',
            'oauth_nonce' => 'NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0',
            'oauth_signature' => 'Yi3V7EvPI8CEDtE3sUZvwWh9xC0=',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1000000000',
            'oauth_version' => '1.0',
        ], $request->toArray());
    }

    public function testOutputsOauth1ParametersAsHtmlFormInputs(): void
    {
        $request = $this->signerFactory
            ->create(new Oauth1Credentials('my-client', 'my-secret'))
            ->sign('POST', 'https://example.com/');

        $this->assertSame(<<<EOHTML
        <input type="hidden" name="oauth_consumer_key" value="my-client"/>
        <input type="hidden" name="oauth_nonce" value="NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0NDQ0"/>
        <input type="hidden" name="oauth_signature" value="6w+On/hrM4ijTwIQDyCylJv3sUE="/>
        <input type="hidden" name="oauth_signature_method" value="HMAC-SHA1"/>
        <input type="hidden" name="oauth_timestamp" value="1000000000"/>
        <input type="hidden" name="oauth_version" value="1.0"/>
        EOHTML, $request->toHtmlFormInputs());
    }
}
