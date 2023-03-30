<?php

namespace Tests\Unit\Lti\Oauth1;

use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1Signer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\Randomizer;
use Tests\Stub\ClockStub;
use Tests\Stub\RandomEngineStub;

#[CoversClass(Oauth1Signer::class)]
#[CoversClass(Oauth1Credentials::class)]
#[CoversClass(Oauth1Request::class)]
final class Oauth1SignerTest extends TestCase
{
    private Oauth1Signer $signer;

    protected function setUp(): void
    {
        $this->signer = new Oauth1Signer(
            new ClockStub(),
            new Randomizer(new RandomEngineStub()),
        );
    }

    public function testSignsOauth1Requests(): void
    {
        $credentials = new Oauth1Credentials('my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/');

        $request = $this->signer->sign($request, $credentials);

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
        $credentials = new Oauth1Credentials('my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/', [
            'lti_version' => 'LTI-1p0',
        ]);

        $request = $this->signer->sign($request, $credentials);

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
        $credentials = new Oauth1Credentials('my-client', 'my-secret');
        $request = new Oauth1Request('POST', 'https://example.com/');

        $request = $this->signer->sign($request, $credentials);

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
