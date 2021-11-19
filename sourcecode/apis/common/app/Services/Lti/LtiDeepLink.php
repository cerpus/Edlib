<?php

namespace App\Services\Lti;

use Firebase\JWT\JWT;
use IMSGlobal\LTI\LTI_Exception;
use IMSGlobal\LTI\LTI_Message_Launch;

class LtiDeepLink
{
    public function __construct(
        private $registration,
        private $deployment_id,
        private $deep_link_settings
    )
    {}

    /**
     * @throws LTI_Exception
     */
    public static function fromLtiLaunch(LTI_Message_Launch $launch): LtiDeepLink
    {
        $db = new LtiDatabase();

        $registration = $db->find_registration_by_issuer($launch->get_launch_data()['iss']);

        if (empty($registration)) {
            throw new LTI_Exception("Registration not found.", 1);
        }

        return new LtiDeepLink(
            $registration,
            $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/deployment_id'],
            $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings']
        );
    }

    public function get_response_jwt($resources)
    {
        $message_jwt = [
            "iss" => $this->registration->get_client_id(),
            "aud" => [$this->registration->get_issuer()],
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

        return JWT::encode($message_jwt, $this->registration->get_tool_private_key(), 'RS256', $this->registration->get_kid());
    }
}
