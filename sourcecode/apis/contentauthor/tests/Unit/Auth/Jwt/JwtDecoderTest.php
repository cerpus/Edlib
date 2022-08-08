<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Jwt;

use App\Auth\Jwt\JwtDecoder;
use App\Auth\Jwt\JwtException;
use ArrayObject;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use function file_get_contents;
use function get_object_vars;

/**
 * Use a tool like jwt.io if you need to add or change something. The private
 * key for generating JWTs exists in the files/ directory.
 *
 * @todo Test leeway
 */
final class JwtDecoderTest extends TestCase
{
    private Client $client;

    /** @var ArrayObject<int, array{request: RequestInterface}> */
    private ArrayObject $history;

    private HttpFactory $httpFactory;

    private MockHandler $mockedResponses;

    protected function setUp(): void
    {
        $this->httpFactory = new HttpFactory();

        $this->mockedResponses = new MockHandler();
        $handler = HandlerStack::create($this->mockedResponses);

        $this->history = new ArrayObject();
        $historyMiddleware = Middleware::history($this->history);
        $handler->push($historyMiddleware);

        $this->client = new Client(['handler' => $handler]);
    }

    private function getJwtDecoder(string $publicKeyOrJwksUri): JwtDecoder
    {
        return new JwtDecoder(
            $publicKeyOrJwksUri,
            $this->client,
            $this->httpFactory,
            new ArrayAdapter(3600, false),
        );
    }

    public function testDecodeValidJwtWithValidKey(): void
    {
        $decoder = $this->getJwtDecoder($this->file('test.key.pub'));
        $payload = $decoder->getVerifiedPayload($this->file('valid-jwt.txt'));

        $this->assertEqualsCanonicalizing([
            'sub' => '1234567890',
            'name' => 'John Lennon',
            'beatle' => true,
            'iat' => 1111111111,
        ], get_object_vars($payload));
    }

    public function testDecodeValidJwtWithLegacyKey(): void
    {
        $this->expectNotToPerformAssertions();

        $decoder = $this->getJwtDecoder($this->file('test-legacy-key-format.key.pub'));
        $decoder->getVerifiedPayload($this->file('valid-jwt.txt'));
    }

    public function testDecodingInvalidJwtThrows(): void
    {
        $decoder = $this->getJwtDecoder($this->file('test.key.pub'));

        $this->expectException(JwtException::class);

        $decoder->getVerifiedPayload($this->file('invalid-jwt.txt'));
    }

    public function testDecodeValidJwtWithJwks(): void
    {
        $this->mockedResponses->append(
            $this->httpFactory->createResponse()->withBody(
                $this->httpFactory->createStream($this->file('jwks.json')),
            ),
        );

        $decoder = $this->getJwtDecoder('http://example.com/.well-known/jwks.json');
        $payload = $decoder->getVerifiedPayload($this->file('valid-jwt.txt'));

        $this->assertArrayHasKey(0, $this->history);
        $this->assertSame(
            'http://example.com/.well-known/jwks.json',
            $this->history[0]['request']->getUri()->__toString(),
        );
        $this->assertEqualsCanonicalizing([
            'sub' => '1234567890',
            'name' => 'John Lennon',
            'beatle' => true,
            'iat' => 1111111111,
        ], get_object_vars($payload));
    }

    private function file(string $filename): string
    {
        return @file_get_contents(__DIR__ . '/files/' . $filename)
            ?: throw new InvalidArgumentException("Couldn't read $filename");
    }
}
