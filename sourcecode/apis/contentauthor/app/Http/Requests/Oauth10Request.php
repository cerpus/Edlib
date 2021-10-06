<?php

namespace App\Http\Requests;

use App\OauthVerifier;

class Oauth10Request {
    private $method;
    private $uri;
    private $params;
    private $authorizationHeader;
    private $authorizationParams;
    private $combinedParams;

    public function __construct($method, $uri, $params, $authorization) {
        $this->method = $method;
        $this->uri = $uri;
        $this->params = $params;
        $this->authorizationHeader = $authorization;
        $this->decodeAuthorization();
        $this->combinedParams = array_merge($this->params, $this->authorizationParams);
    }

    public function decodeAuthorization() {
        $this->authorizationParams = [];
        if (!$this->authorizationHeader) {
            return;
        }
        $authorization = trim($this->authorizationHeader);
        if (strlen($authorization) < 6 || strtolower(trim(substr($authorization, 0, 6))) != 'oauth') {
            return;
        }
        $authorization = substr($authorization, 6);
        $authorization = explode(',', $authorization);
        $authorization = array_filter(array_map(function ($pairStr) {
            $pairStr = trim($pairStr);
            $pos = strpos($pairStr, '=');
            if ($pos && $pos > 0) {
                $name = substr($pairStr, 0, $pos);
                $enclosedValue = substr($pairStr, $pos + 1);
                if (strlen($enclosedValue) > 1 && substr($enclosedValue, 0, 1) == '"' && substr($enclosedValue, -1) == '"') {
                    $value = substr($enclosedValue, 1, -1);
                    return ['name' => $name, 'value' => $value];
                }
            }
            return null;
        }, $authorization), function ($entry) {
            return $entry ? true : false;
        });
        $firstItem = array_shift($authorization);
        if ($firstItem['name'] != 'realm') {
            array_unshift($authorization, $firstItem);
        }
        $authorization = array_map(function ($entry) {
            return [
                'name' => $entry['name'],
                'value' => rawurldecode($entry['value'])
            ];
        }, $authorization);
        foreach ($authorization as $entry){
            $this->authorizationParams[$entry['name']] = $entry['value'];
        }
    }

    public function getConsumerKey() {
        return $this->combinedParams['oauth_consumer_key'];
    }

    public function validateOauth10($consumerKey, $consumerSecret) {
        if ($consumerKey == $this->getConsumerKey()) {
            $oauth = new OauthVerifier($consumerSecret);
            $params = $this->combinedParams;
            $expectedSignature = $params['oauth_signature'];
            unset($params['oauth_signature']);
            $signature = $oauth->generateSignature($this->method, $this->uri, $params);
            return ($signature === $expectedSignature);
        } else {
            return false;
        }
    }
}
