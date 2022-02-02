<?php

namespace App\Oauth10;

class OauthSignature
{
    public function __construct(private string $consumerSecret)
    {
    }

    /**
     * @param array{oauth_signature_method: string} $params
     * @throws UnsupportedSignatureException
     */
    public function generateSignature(string $method, string $uri, array $params): string
    {
        $signatureMethod = $params['oauth_signature_method'];

        if ($signatureMethod !== "HMAC-SHA1") {
            throw new UnsupportedSignatureException($signatureMethod);
        }

        $signatureSecret = oauth_urlencode($this->consumerSecret) . "&";
        $signatureBaseString = oauth_get_sbs($method, $uri, $params);
        $hash = hash_hmac("sha1", $signatureBaseString, $signatureSecret, true);

        return base64_encode($hash);
    }
}
