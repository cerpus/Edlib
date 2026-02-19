<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

use BadMethodCallException;
use InvalidArgumentException;

use function array_keys;
use function array_map;
use function htmlspecialchars;
use function implode;
use function ksort;
use function parse_url;
use function rawurlencode;
use function sprintf;
use function strtoupper;

use const ENT_HTML5;
use const ENT_QUOTES;
use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_PORT;
use const PHP_URL_SCHEME;

class Request
{
    /**
     * @param string[] $parameters
     *     The combined POST data and OAuth header fields from the request.
     *     Query parameters will be inferred from the URL, and should not be
     *     passed explicitly.
     */
    public function __construct(
        private readonly string $method,
        private readonly string $url,
        private array $parameters = [],
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function has(string $parameter): bool
    {
        return isset($this->parameters[$parameter]);
    }

    public function get(string $parameter): string
    {
        return $this->parameters[$parameter]
            ?? throw new BadMethodCallException("Missing parameter '$parameter'");
    }

    public function with(string $parameter, string $value): static
    {
        $self = clone $this;
        $self->parameters = [...$self->parameters, $parameter => $value];
        ksort($self->parameters);

        return $self;
    }

    public function without(string $parameter): static
    {
        $self = clone $this;
        unset($self->parameters[$parameter]);

        return $self;
    }

    public function toHtmlFormInputs(): string
    {
        return implode("\n", array_map(function (string $value, string $key): string {
            return sprintf(
                '<input type="hidden" name="%s" value="%s"/>',
                htmlspecialchars($key, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            );
        }, $this->parameters, array_keys($this->parameters)));
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->parameters;
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
            $this->parameters,
            array_keys($this->parameters),
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
