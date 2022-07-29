<?php

declare(strict_types=1);

namespace App\Auth\Jwt;

use Exception;
use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use stdClass;
use function preg_match;
use function sprintf;
use function str_split;
use function str_starts_with;
use function strtr;

final class JwtDecoder implements JwtDecoderInterface
{
    /**
     * @param string $publicKeyOrJwksUri A URI to a JWKS endpoint, or a public
     *     key.
     */
    public function __construct(
        private readonly string $publicKeyOrJwksUri,
        private readonly ClientInterface $client,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly CacheItemPoolInterface $cache,
        private readonly int $leewaySeconds = 0,
    ) {
    }

    public function getVerifiedPayload(string $bearerToken): stdClass
    {
        $key = $this->getKeyOrKeySet();
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = $this->leewaySeconds;

        try {
            return JWT::decode($bearerToken, $key);
        } catch (Exception $e) {
            // The JWT library does not have its own exception class hierarchy,
            // so the broad catching is fine in this case.
            throw new JwtException($e->getMessage(), 0, $e);
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    private function getKeyOrKeySet(): CachedKeySet|Key
    {
        if (preg_match('@^https?://@i', $this->publicKeyOrJwksUri)) {
            return new CachedKeySet(
                $this->publicKeyOrJwksUri,
                $this->client,
                $this->requestFactory,
                $this->cache,
            );
        }

        $publicKey = $this->publicKeyOrJwksUri;

        if (!str_starts_with($publicKey, "-----BEGIN ")) {
            // Turns a non-armoured, possibly-uri-safe, possibly-padded
            // base64-encoded key into a well-formatted PEM key
            $publicKey = sprintf(
                "-----BEGIN PUBLIC KEY-----\n%s\n-----END PUBLIC KEY-----",
                implode("\n", str_split(base64_encode(base64_decode(
                    strtr($publicKey, '-_', '+/'),
                )), 64)),
            );
        }

        return new Key($publicKey, 'RS256');
    }
}
