<?php

namespace App\Traits;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriNormalizer;
use Ramsey\Uuid\Uuid;

trait LtiUrlFunctions
{
    /**
     * Return a launch ID from a launch URL. Launch URL may be a launch ID or a full launch URL
     * @param string $launchUrl
     * @return string|null
     */
    public function launchId(string $launchUrl): ?string
    {
        return $this->pickLaunchIdFromLaunchUrl($launchUrl);
    }

    /**
     * Return a launch URL from an id. If $launchId is an url the id will be picked from the url and a new url constructed
     * @param  string  $launchId
     * @return string|null
     */
    public function launchUrl(string $launchId): ?string
    {
        $launchUrl = null;
        if ($lId = $this->pickLaunchIdFromLaunchUrl($launchId)) {
            $url = new Uri(config('edlib.url') . '/' . config('edlib.launchPath') . '/' . $lId);
            $launchUrl = UriNormalizer::normalize($url, UriNormalizer::REMOVE_DUPLICATE_SLASHES);
        }

        return $launchUrl;
    }

    public function isUrl(string $launchId): bool
    {
        return filter_var($launchId, FILTER_VALIDATE_URL);
    }

    public function pickLaunchIdFromLaunchUrl(string $launchUrl): ?string
    {
        $launchId = null;
        if (Uuid::isValid($launchUrl)) {
            $launchId = $launchUrl;
        }

        if ($this->isUrl($launchUrl)) {
            $path = parse_url($launchUrl, PHP_URL_PATH);
            $url = explode('/', $path);
            $lastElement = end($url);

            $launchId = null;
            if (Uuid::isValid($lastElement)) {
                $launchId = $lastElement;
            }
        }

        return $launchId;
    }
}
