<?php

namespace App\Libraries\OAuthAdapter;


use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Http\Uri\Uri;
use OAuth\OAuth1\Signature\Signature;

class OAuthHeaderFactory {
    private $consumerId;
    private $consumerSecret;

    public function __construct($consumerId, $consumerSecret) {
        $this->consumerId = $consumerId;
        $this->consumerSecret = $consumerSecret;
    }

    public function generateNonce() {
        return sha1(mt_rand().mt_rand());
    }

    public function getSignatureMethod() {
        return "HMAC-SHA1";
    }

    public function getAuthorizationParameters($method, $uri, $params = []) {
        $dateTime = new \DateTime();
        $basicParameters = array(
            'oauth_consumer_key'     => $this->consumerId,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_signature_method' => $this->getSignatureMethod(),
            'oauth_timestamp'        => $dateTime->format('U'),
            'oauth_version'          => '1.0'
        );
        $uri = new Uri($uri);
        $allParams = array_merge($params, $basicParameters);
        $signatureInterface = new Signature(new Credentials($this->consumerId, $this->consumerSecret, null));
        $signatureInterface->setHashingAlgorithm($this->getSignatureMethod());
        $basicParameters['oauth_signature'] = $signatureInterface->getSignature($uri, $allParams, $method);
        return $basicParameters;
    }
}