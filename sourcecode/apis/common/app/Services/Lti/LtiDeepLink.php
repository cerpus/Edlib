<?php

namespace App\Services\Lti;

use App\Models\LtiRegistration;
use Firebase\JWT\JWT;

class LtiDeepLink
{
    public function __construct(
        private LtiRegistration $registration,
        private $deployment_id,
        private $deep_link_settings
    )
    {}

    public function getResponseJwt($resources)
    {
        $message_jwt = [
            "iss" => $this->registration->client_id,
            "aud" => [$this->registration->issuer],
            "exp" => time() + 600,
            "iat" => time(),
            "nonce" => 'nonce' . hash('sha256', random_bytes(64)),
            "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => $this->deployment_id,
            "https://purl.imsglobal.org/spec/lti/claim/message_type" => "LtiDeepLinkingResponse",
            "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
            "https://purl.imsglobal.org/spec/lti-dl/claim/content_items" => array_map(function ($resource) {
                return $resource->to_array();
            }, $resources),
        ];

        if (array_key_exists('data', $this->deep_link_settings)) {
            $message_jwt['https://purl.imsglobal.org/spec/lti-dl/claim/data'] = $this->deep_link_settings['data'];
        }

        $key = $this->registration->ltiKeySet->newestKey;

        return JWT::encode($message_jwt, $key->private_key, $key->algorithm, $key->id);
    }
}
