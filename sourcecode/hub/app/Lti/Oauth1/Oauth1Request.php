<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use BadMethodCallException;
use function array_combine;
use function array_keys;
use function array_map;
use function htmlspecialchars;
use function implode;
use function ksort;
use function rawurlencode;
use function sprintf;
use function strtoupper;
use const ENT_HTML5;
use const ENT_QUOTES;

class Oauth1Request
{
    /**
     * @param string[] $parameters
     *     The combined query and POST data from the request
     */
    public function __construct(
        private readonly string $method,
        private readonly string $url,
        private array $parameters = [],
    ) {
        ksort($this->parameters);
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
        // https://datatracker.ietf.org/doc/html/rfc5849#section-3.4.1.3.2
        $parameters = array_combine(
            array_map(rawurlencode(...), array_keys($this->parameters)),
            array_map(rawurlencode(...), $this->parameters),
        );
        ksort($parameters);

        return strtoupper($this->method) .
            '&' . rawurlencode($this->url) .
            '&' . rawurlencode(implode('&', array_map(
                fn (string $value, string $key): string => "$key=$value",
                $parameters,
                array_keys($parameters),
            )));
    }
}
