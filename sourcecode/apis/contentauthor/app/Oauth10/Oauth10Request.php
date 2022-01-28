<?php

namespace App\Oauth10;

class Oauth10Request
{
    private array $combinedParams;

    public function __construct(
        private string $method,
        private string $uri,
        private array $params,
        string $authorizationHeader,
    ) {
        $authorizationParams = self::decodeAuthorization($authorizationHeader);
        $this->combinedParams = array_replace($this->params, $authorizationParams);
    }

    /**
     * @throws UnsupportedSignatureException
     * @throws MissingSignatureException
     */
    public function validateOauth10(string $consumerKey, string $consumerSecret): bool
    {
        if ($consumerKey !== $this->combinedParams['oauth_consumer_key'] ?? null) {
            return false;
        }

        $params = $this->combinedParams;
        $expectedSignature = $params['oauth_signature'] ?? throw new MissingSignatureException();
        unset($params['oauth_signature']);

        $oauth = new OauthSignature($consumerSecret);
        $signature = $oauth->generateSignature($this->method, $this->uri, $params);

        return hash_equals($signature, $expectedSignature);
    }

    /**
     * @see https://datatracker.ietf.org/doc/html/rfc5849#section-3.5.1
     */
    private static function decodeAuthorization(string $authorization): array
    {
        if (!str_starts_with(strtolower($authorization), 'oauth ')) {
            return [];
        }

        preg_match_all('/\b(oauth_\w+)="(.*?)"/', $authorization, $matches, PREG_SET_ORDER);

        $params = [];
        foreach ($matches as [1 => $key, 2 => $value]) {
            $params[$key] = rawurldecode($value);
        }

        return $params;
    }
}
