<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Jwt;

use App\Support\Jwt\JwtDecoder;
use App\Support\Jwt\JwtException;
use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp\Client;
use Http\Factory\Guzzle\RequestFactory;
use PHPUnit\Framework\TestCase;

use function assert;
use function file_get_contents;

final class JwtDecoderTest extends TestCase
{
    private Client $client;
    private JwtDecoder $jwtDecoder;

    protected function setUp(): void
    {
        $this->client = new Client();

        $this->jwtDecoder = new JwtDecoder(
            $this->client,
            new RequestFactory(),
            new ArrayCachePool(),
        );
    }

    public function testGetsValidatedPayload(): void
    {
        $jwt = file_get_contents(__DIR__ . '/files/valid-jwt.txt');
        assert($jwt);

        $pubkey = file_get_contents(__DIR__ . '/files/test.key.pub');
        assert($pubkey);

        $payload = $this->jwtDecoder->getVerifiedPayload($jwt, $pubkey);

        $this->assertObjectHasProperty('name', $payload);
        $this->assertSame('Jason Webb Tokin', $payload->name);
        $this->assertSame('jason@example.com', $payload->email);
    }

    public function testThrowsOnInvalidKey(): void
    {
        $jwt = file_get_contents(__DIR__ . '/files/valid-jwt.txt');
        assert($jwt);

        $pubkey = file_get_contents(__DIR__ . '/files/bad.key.pub');
        assert($pubkey);

        $this->expectException(JwtException::class);

        $this->jwtDecoder->getVerifiedPayload($jwt, $pubkey);
    }
}
