<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 25.05.16
 * Time: 12:41
 */

namespace App;


class OauthVerifier
{
    private $consumerSecret;

    public function __construct($consumerSecret)
    {
        $this->consumerSecret = $consumerSecret;
    }

    public function generateSignature($method, $uri, $params) {
        $signatureMethod = $params['oauth_signature_method'];
        $signatureSecret = \oauth_urlencode($this->consumerSecret) . "&";
        $signatureBaseString = \oauth_get_sbs($method, $uri, $params);
        if ($signatureMethod === "HMAC-SHA1") {
            $hash = hash_hmac("sha1", $signatureBaseString, $signatureSecret, true);
            return base64_encode($hash);
        } else {
            throw new \Exception("Oauth: Unsupported signature method ".$signatureMethod);
        }
    }
}