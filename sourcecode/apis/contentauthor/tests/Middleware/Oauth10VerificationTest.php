<?php

use Tests\TestCase;

class Oauth10VerificationTest extends TestCase
{
    public function testAuthorizationHeader()
    {
        $authorizeHeader = "OAuth realm=\"Photos\",\n" .
            "        oauth_consumer_key=\"dpf43f3p2l4k3l03\",\n" .
            "        oauth_signature_method=\"HMAC-SHA1\",\n" .
            "        oauth_timestamp=\"137131200\",\n" .
            "        oauth_nonce=\"wIjqoS\",\n" .
            "        oauth_callback=\"http%3A%2F%2Fprinter.example.com%2Fready\",\n" .
            "        oauth_signature=\"74KNZJeDHnMBp0EMJ9ZHt%2FXKycU%3D\"";
        $consumerKey = "dpf43f3p2l4k3l03";
        $secret = "kd94hf93k423kf44";
        $url = "https://photos.example.net/initiate";
        $method = "POST";

        $oauthRequest = new \App\Http\Requests\Oauth10Request($method, $url, [], $authorizeHeader);
        $this->assertTrue($oauthRequest->validateOauth10($consumerKey, $secret));
    }

    public function testAuthorizationHeaderWithoutRealm()
    {
        $authorizeHeader = "OAuth oauth_consumer_key=\"dpf43f3p2l4k3l03\",\n" .
            "        oauth_signature_method=\"HMAC-SHA1\",\n" .
            "        oauth_timestamp=\"137131200\",\n" .
            "        oauth_nonce=\"wIjqoS\",\n" .
            "        oauth_callback=\"http%3A%2F%2Fprinter.example.com%2Fready\",\n" .
            "        oauth_signature=\"74KNZJeDHnMBp0EMJ9ZHt%2FXKycU%3D\"";
        $consumerKey = "dpf43f3p2l4k3l03";
        $secret = "kd94hf93k423kf44";
        $url = "https://photos.example.net/initiate";
        $method = "POST";

        $oauthRequest = new \App\Http\Requests\Oauth10Request($method, $url, [], $authorizeHeader);
        $this->assertTrue($oauthRequest->validateOauth10($consumerKey, $secret));
    }
}
