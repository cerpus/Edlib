<?php

declare(strict_types=1);

namespace App\Support\Jwt;

use Exception;
use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use stdClass;

use function preg_match;
use function str_starts_with;

final readonly class JwtDecoder implements JwtDecoderInterface
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private CacheItemPoolInterface $cache,
    ) {}

    public function getVerifiedPayload(
        string $bearerToken,
        string $publicKeyOrJwksUri,
        int $leewaySeconds = 300,
        int|null $cacheExpirySeconds = null,
    ): stdClass {
        $key = $this->getKeyOrKeySet($publicKeyOrJwksUri, $cacheExpirySeconds);
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $leewaySeconds;

        try {
            return JWT::decode($bearerToken, $key);
        } catch (Exception $e) {
            throw new JwtException($e->getMessage(), 0, $e);
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    private function getKeyOrKeySet(
        string $publicKeyOrJwksUri,
        int|null $cacheExpirySeconds,
    ): CachedKeySet|Key {
        if (preg_match('@^https?://@i', $publicKeyOrJwksUri)) {
            return new CachedKeySet(
                $publicKeyOrJwksUri,
                $this->client,
                $this->requestFactory,
                $this->cache,
                $cacheExpirySeconds,
                rateLimit: true,
            );
        }

        if (!str_starts_with($publicKeyOrJwksUri, '-----BEGIN ')) {
            // Turns a non-armoured, possibly-uri-safe, possibly-padded
            // base64-encoded key into a well-formatted PEM key
            $publicKeyOrJwksUri = sprintf(
                "-----BEGIN PUBLIC KEY-----\n%s\n-----END PUBLIC KEY-----",
                implode("\n", str_split(base64_encode(base64_decode(
                    strtr($publicKeyOrJwksUri, '-_', '+/'),
                )), 64)),
            );
        }

        return new Key($publicKeyOrJwksUri, 'RS256');
    }
}
