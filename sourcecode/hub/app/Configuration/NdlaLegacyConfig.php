<?php

declare(strict_types=1);

namespace App\Configuration;

use App\Models\LtiPlatform;
use BadMethodCallException;
use GuzzleHttp\ClientInterface;

use function is_string;
use function parse_url;

use const PHP_URL_HOST;
use const PHP_URL_PATH;

final readonly class NdlaLegacyConfig
{
    public function __construct(
        private string|null $domain,
        private string|null $contentAuthorHost,
        private ClientInterface|null $contentAuthorClient,
        private string|null $publicKeyOrJwksUri,
        private string|null $internalLtiPlatformKey,
    ) {}

    public function isEnabled(): bool
    {
        return $this->domain !== null;
    }

    public function getDomain(): string
    {
        if ($this->domain === null) {
            throw new BadMethodCallException('NDLA legacy support is disabled');
        }

        return $this->domain;
    }

    public function extractEdlib2IdFromUrl(string $url): string|null
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($host) || !is_string($path)) {
            return null;
        }

        if (
            $host !== $this->getDomain() ||
            !preg_match('!^/resource/([^/]+)!', $path, $matches)
        ) {
            return null;
        }

        return $matches[1];
    }

    public function getContentAuthorHost(): string
    {
        if ($this->contentAuthorHost === null) {
            throw new BadMethodCallException('$contentAuthorHost must be set');
        }

        return $this->contentAuthorHost;
    }

    public function extractH5pIdFromUrl(string $url): string|null
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($host) || !is_string($path)) {
            return null;
        }

        if (
            $host !== $this->getContentAuthorHost() ||
            !preg_match('!^/h5p/(\d+)\b!', $path, $matches)
        ) {
            return null;
        }

        return $matches[1];
    }

    public function getContentAuthorClient(): ClientInterface
    {
        if ($this->contentAuthorClient === null) {
            throw new BadMethodCallException('$contentAuthorClient must be set');
        }

        return $this->contentAuthorClient;
    }

    public function getPublicKeyOrJwksUri(): string
    {
        if ($this->publicKeyOrJwksUri === null) {
            throw new BadMethodCallException('$publicKeyOrJwksUri must be set');
        }

        return $this->publicKeyOrJwksUri;
    }

    public function getInternalLtiPlatform(): LtiPlatform
    {
        $key = $this->internalLtiPlatformKey;

        if ($key === null) {
            throw new BadMethodCallException('$internalLtiPlatformKey must be set');
        }

        return LtiPlatform::where('key', $key)->firstOr(function () use ($key) {
            $platform = new LtiPlatform();
            $platform->key = $key;
            $platform->name = 'NDLA internal';
            $platform->authorizes_edit = true;
            $platform->enable_sso = true;
            $platform->save();

            return $platform;
        });
    }
}
