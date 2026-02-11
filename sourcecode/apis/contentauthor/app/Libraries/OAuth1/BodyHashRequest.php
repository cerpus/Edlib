<?php

declare(strict_types=1);

namespace App\Libraries\OAuth1;

use Cerpus\EdlibResourceKit\Oauth1\Claim;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\HeaderBag;

final class BodyHashRequest {
    private array $oauthAuthParams = [];

    public function __construct(
        private readonly string $method,
        private readonly string $url,
        private string $content = '',
        private readonly HeaderBag $headers = new HeaderBag(),
    ) {
        $this->decodeAuthorizationHeader();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getHeaders(): HeaderBag
    {
        return $this->headers;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getHeader(string $key, ?string $default = null): ?string
    {
        return urldecode($this->headers->get($key, $default));
    }

    public function hasHeader(string $key): bool
    {
        return $this->headers->has($key);
    }

    public function setHeader(string $key, string|array|null $values, bool $replace = true): void
    {
        $this->headers->set($key, $values, $replace);
    }

    public function isOAuth1(): bool
    {
        return ($this->hasHeader('Authorization') && str_starts_with($this->getHeader('Authorization'), 'OAuth '));
    }

    public function getOAuthParameters(): array
    {
        return $this->oauthAuthParams;
    }

    public function hasOAuthParameter(string $key): bool
    {
        return array_key_exists($key, $this->oauthAuthParams);
    }

    public function getOAuthParameter(string $key, ?string $default = null): ?string
    {
        $params = $this->getOAuthParameters();
        return $params[$key] ?? $default;
    }

    public function setContent(string $content): BodyHashRequest
    {
        $self = clone $this;
        $self->content = $content;

        return $self->setOAuthParameter('oauth_body_hash', $self->generateBodyHash());
    }

    public function setOAuthParameter(string $key, string $value): BodyHashRequest
    {
        $self = clone $this;
        $self->oauthAuthParams[$key] = $value;
        ksort($self->oauthAuthParams);

        return $self;
    }

    public function removeOAuthParameter(string $key): BodyHashRequest
    {
        $self = clone $this;
        unset($self->oauthAuthParams[$key]);

        return $self;
    }

    public function decodeAuthorizationHeader(): void
    {
        if ($this->isOAuth1()) {
            $authHeader = $this->getHeader('Authorization');
            $matches = [];
            preg_match_all('/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/', $authHeader, $matches);
            if (array_key_exists(2, $matches) && array_key_exists(4, $matches)) {
                $this->oauthAuthParams = array_combine($matches[2], $matches[4]);
                ksort($this->oauthAuthParams);
            }
        }
    }

    public function generateBodyHash(): string
    {
        return base64_encode(sha1($this->content, true));
    }

    public function createAuthorizationHeader(): string
    {
        return sprintf(
            'OAuth realm="%s",oauth_version="%s",oauth_nonce="%s",oauth_timestamp="%s",oauth_consumer_key="%s",oauth_body_hash="%s",oauth_signature_method="%s",oauth_signature="%s"',
            $this->getUrl(),
            $this->getOAuthParameter(Claim::VERSION),
            rawurlencode($this->getOAuthParameter(Claim::NONCE)),
            $this->getOAuthParameter(Claim::TIMESTAMP),
            $this->getOAuthParameter(Claim::CONSUMER_KEY),
            rawurlencode($this->generateBodyHash()),
            $this->getOAuthParameter(Claim::SIGNATURE_METHOD),
            rawurlencode($this->getOAuthParameter(Claim::SIGNATURE)),
        );
    }

    public function generateSignatureBaseString(): string
    {
        return strtoupper($this->method) .
            '&' . rawurlencode($this->getBaseStringUrl()) .
            '&' . rawurlencode($this->getBaseStringParameters());
    }

    /**
     * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.2
     */
    private function getBaseStringUrl(): string
    {
        $scheme = parse_url($this->url, PHP_URL_SCHEME)
            ?: throw new InvalidArgumentException('Missing scheme');
        $host = parse_url($this->url, PHP_URL_HOST)
            ?: throw new InvalidArgumentException('Missing host');
        $port = parse_url($this->url, PHP_URL_PORT);
        $path = parse_url($this->url, PHP_URL_PATH) ?: '/';

        return "$scheme://$host" . ($port !== null && (
                $scheme === 'http' && $port !== 80 ||
                $scheme === 'https' && $port !== 443
            ) ? ":$port" : '') . $path;
    }

    /**
     * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.3
     */
    private function getBaseStringParameters(): string
    {
        // convert parameters to [key, value] arrays
        $parameters = array_map(
            fn (string $value, int|string $key) => [(string) $key, $value],
            $this->oauthAuthParams,
            array_keys($this->oauthAuthParams),
        );

        // add parameters from query, if any
        $query = parse_url($this->url, PHP_URL_QUERY);
        if (is_string($query)) {
            $parameters = [
                ...$parameters,
                ...array_map(function (string $pair) {
                    $pair = explode('=', $pair);

                    return [urldecode($pair[0]), urldecode($pair[1] ?? '')];
                }, explode('&', $query)),
            ];
        }

        // remove signature claims
        $parameters = array_filter(
            $parameters,
            fn ($pair) => $pair[0] !== Claim::SIGNATURE,
        );

        // encode pairs, then sort
        $parameters = array_map(
            fn (array $pair) => [rawurlencode($pair[0]), rawurlencode($pair[1])],
            $parameters,
        );
        usort(
            $parameters,
            fn ($a, $b) => strcmp($a[0], $b[0]) ?: strcmp($a[1], $b[1]),
        );

        // combine result into query string
        return implode('&', array_map(function (array $pair) {
            return $pair[0] . '=' . $pair[1];
        }, $parameters));
    }
}
