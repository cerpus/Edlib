<?php

namespace App\Services\Lti;

use App\Exceptions\NotFoundException;
use App\Models\LtiRegistration;
use App\Services\Lti\Validators\DeepLinkMessageValidator;
use App\Services\Lti\Validators\ResourceMessageValidator;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use IMSGlobal\LTI\Cache;
use IMSGlobal\LTI\Cookie;
use IMSGlobal\LTI\LTI_Exception;

class LtiMessageLaunch
{

    private Cache $cache;
    private $request;
    private $cookie;
    private $jwt;
    public LtiRegistration $registration;
    private $launch_id;

    /**
     * Constructor
     *
     */
    function __construct()
    {
        $this->launch_id = uniqid("lti1p3_launch_", true);

        $this->cache = new Cache();
        $this->cookie = new Cookie();
    }

    /**
     * Load an LTI_Message_Launch from a Cache using a launch id.
     */
    public static function fromCache(string $launch_id)
    {
        $new = new LtiMessageLaunch();
        $new->launch_id = $launch_id;
        $new->jwt = ['body' => $new->cache->get_launch_data($launch_id)];
        return $new->validateRegistration();
    }

    /**
     * Load an LTI_Message_Launch from a Cache using a launch id.
     * @throws LTI_Exception
     */
    public static function fromRequest(array $request = null): LtiMessageLaunch
    {
        $new = new LtiMessageLaunch();
        return $new->validate($request);
    }

    /**
     * Validates all aspects of an incoming LTI message launch and caches the launch if successful.
     *
     * @return LtiMessageLaunch     Will return $this if validation is successful.
     * @throws LTI_Exception Will throw an LTI_Exception if validation fails.
     * @throws NotFoundException
     */
    public function validate(array $request = null): LtiMessageLaunch
    {
        $this->request = $request;

        return $this->validateState()
            ->validateJwtFormat()
            ->validateNonce()
            ->validateRegistration()
            ->validateJwtSignature()
            ->validateDeployment()
            ->validateMessage()
            ->cacheLaunchData();
    }

    /**
     * Returns whether or not the current launch is a deep linking launch.
     *
     * @return boolean  Returns true if the current launch is a deep linking launch.
     */
    public function isDeepLinkLaunch(): bool
    {
        return $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiDeepLinkingRequest';
    }

    /**
     * Returns whether or not the current launch is a resource launch.
     *
     * @return boolean  Returns true if the current launch is a resource launch.
     */
    public function isResourceLaunch(): bool
    {
        return $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'] === 'LtiResourceLinkRequest';
    }

    /**
     * Fetches the decoded body of the JWT used in the current launch.
     */
    public function getLaunchData(): array
    {
        return $this->jwt['body'];
    }

    /**
     * Get the unique launch id for the current launch.
     */
    public function getLaunchId(): string
    {
        return $this->launch_id;
    }

    private function getPublicKey(): bool|array
    {
        $key_set_url = $this->registration->platform_key_set_endpoint;

        // Download key set
        $public_key_set = json_decode(file_get_contents($key_set_url), true);

        if (empty($public_key_set)) {
            // Failed to fetch public keyset from URL.
            throw new LTI_Exception("Failed to fetch public key", 1);
        }

        // Find key used to sign the JWT (matches the KID in the header)
        foreach ($public_key_set['keys'] as $key) {
            if ($key['kid'] == $this->jwt['header']['kid']) {
                try {
                    return openssl_pkey_get_details(JWK::parseKey($key));
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        // Could not find public key with a matching kid and alg.
        throw new LTI_Exception("Unable to find public key", 1);
    }

    private function cacheLaunchData(): LtiMessageLaunch
    {
        $this->cache->cache_launch_data($this->launch_id, $this->jwt['body']);
        return $this;
    }

    private function validateState(): LtiMessageLaunch
    {
        // Check State for OIDC.
        if ($this->cookie->get_cookie('lti1p3_' . $this->request['state']) !== $this->request['state']) {
            // Error if state doesn't match
            throw new LTI_Exception("State not found", 1);
        }
        return $this;
    }

    private function validateJwtFormat(): LtiMessageLaunch
    {
        $jwt = $this->request['id_token'];

        if (empty($jwt)) {
            throw new LTI_Exception("Missing id_token", 1);
        }

        // Get parts of JWT.
        $jwt_parts = explode('.', $jwt);

        if (count($jwt_parts) !== 3) {
            // Invalid number of parts in JWT.
            throw new LTI_Exception("Invalid id_token, JWT must contain 3 parts", 1);
        }

        // Decode JWT headers.
        $this->jwt['header'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[0]), true);
        // Decode JWT Body.
        $this->jwt['body'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[1]), true);

        return $this;
    }

    private function validateNonce(): LtiMessageLaunch
    {
        if (!$this->cache->check_nonce($this->jwt['body']['nonce'])) {
            //throw new LTI_Exception("Invalid Nonce");
        }
        return $this;
    }

    /**
     * @throws \App\Exceptions\NotFoundException
     */
    private function validateRegistration(): LtiMessageLaunch
    {
        $this->registration = LtiRegistration::whereClientId($this->jwt['body']['aud'])->whereIssuer($this->jwt['body']['iss'])->first();

        if (empty($this->registration)) {
            throw new NotFoundException("registration");
        }

        return $this;
    }

    private function validateJwtSignature()
    {
        // Fetch public key.
        $public_key = $this->getPublicKey();

        // Validate JWT signature
        try {
            JWT::decode($this->request['id_token'], $public_key['key'], array('RS256'));
        } catch (\Exception $e) {
            var_dump($e);
            // Error validating signature.
            throw new LTI_Exception("Invalid signature on id_token", 1);
        }

        return $this;
    }

    /**
     * @throws NotFoundException
     */
    private function validateDeployment(): LtiMessageLaunch
    {
        // Find deployment.
        $deploymentId = $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/deployment_id'];
        $deployment = $this->registration->ltiDeployments()->where('deployment_id', $deploymentId)->first();

        if (empty($deployment)) {
            // deployment not recognized.
            throw new NotFoundException("Unable to find deployment");
        }

        return $this;
    }

    private function validateMessage(): LtiMessageLaunch
    {
        if (empty($this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/message_type'])) {
            // Unable to identify message type.
            throw new LTI_Exception("Invalid message type", 1);
        }

        $validators = [
            new DeepLinkMessageValidator(),
            new ResourceMessageValidator()
        ];

        $message_validator = false;
        foreach ($validators as $validator) {
            if ($validator->can_validate($this->jwt['body'])) {
                if ($message_validator !== false) {
                    // Can't have more than one validator apply at a time.
                    throw new LTI_Exception("Validator conflict", 1);
                }
                $message_validator = $validator;
            }
        }

        if ($message_validator === false) {
            throw new LTI_Exception("Unrecognized message type.", 1);
        }

        if (!$message_validator->validate($this->jwt['body'])) {
            throw new LTI_Exception("Message validation failed.", 1);
        }

        return $this;

    }

    public function getDeepLink(): LtiDeepLink
    {
        return new LtiDeepLink(
            $this->registration,
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti/claim/deployment_id'],
            $this->jwt['body']['https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings']
        );
    }
}
