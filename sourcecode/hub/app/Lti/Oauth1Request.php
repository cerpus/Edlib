<?php

declare(strict_types=1);

namespace App\Lti;

use function array_keys;
use function htmlspecialchars;
use function sprintf;

use const ENT_HTML5;
use const ENT_QUOTES;

class Oauth1Request
{
    /** @var string[] */
    private array $data;

    /**
     * @param string[] $parameters
     */
    public function __construct(
        string $consumerKey,
        string $nonce,
        string $signatureMethod,
        int $timestamp,
        array $parameters = [],
    ) {
        $this->data = [
            ...$parameters,
            'oauth_consumer_key' => $consumerKey,
            'oauth_nonce' => $nonce,
            'oauth_signature_method' => $signatureMethod,
            'oauth_timestamp' => (string) $timestamp,
            'oauth_version' => '1.0',
        ];
        ksort($this->data);
    }

    public function withSignature(string $signature): static
    {
        $self = clone $this;
        $self->data = [...$this->data, 'oauth_signature' => $signature];
        ksort($self->data);

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
        }, $this->data, array_keys($this->data)));
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
