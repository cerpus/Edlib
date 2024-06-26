<?php

namespace App\Services\Lti;

use App\Exceptions\NotFoundException;
use App\Models\LtiRegistration;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Packback\Lti1p3\LtiException;
use Packback\Lti1p3\MessageValidators\DeepLinkMessageValidator;
use Packback\Lti1p3\MessageValidators\ResourceMessageValidator;

class LtiMessageLaunch
{
    private Request $request;
    private array $jwt;
    public ?LtiRegistration $registration;
    private string $launch_id;

    public function __construct(string $launchId = null)
    {
        $this->launch_id = $launchId ?? uniqid("lti1p3_launch_", true);
    }

    /**
     * Load an LTI_Message_Launch from a Cache using a launch id.
     * @throws NotFoundException
     */
    public static function fromCache(string $launch_id): LtiMessageLaunch
    {
        $new = new LtiMessageLaunch($launch_id);
        $new->jwt = ['body' => Cache::get($launch_id)];
        return $new->validateRegistration();
    }

    /**
     * Load an LTI_Message_Launch from a Cache using a launch id.
     * @throws LtiException
     */
    public static function fromRequest(Request $request = null): LtiMessageLaunch
    {
        $new = new LtiMessageLaunch();
        return $new->validate($request);
    }

    /**
     * Validates all aspects of an incoming LTI message launch and caches the launch if successful.
     *
     * @return LtiMessageLaunch     Will return $this if validation is successful.
     * @throws LtiException Will throw an LtiException if validation fails.
     * @throws NotFoundException
     */
    public function validate(Request $request = null): LtiMessageLaunch
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

        $public_key_set = Http::get($key_set_url)->json();

        if (empty($public_key_set)) {
            // Failed to fetch public keyset from URL.
            throw new LtiException("Failed to fetch public key", 1);
        }

        // Find key used to sign the JWT (matches the KID in the header)
        foreach ($public_key_set['keys'] as $key) {
            if ($key['kid'] == $this->jwt['header']['kid']) {
                return openssl_pkey_get_details(
                    JWK::parseKey($key)->getKeyMaterial(),
                );
            }
        }

        // Could not find public key with a matching kid and alg.
        throw new LtiException("Unable to find public key", 1);
    }

    private function cacheLaunchData(): LtiMessageLaunch
    {
        Cache::set($this->launch_id, $this->jwt['body']);

        return $this;
    }

    private function validateState(): LtiMessageLaunch
    {
        // Check State for OIDC.
        if (!$this->request->get('state') || $this->request->cookie('lti1p3_' . $this->request->get('state')) !== $this->request->get('state')) {
            // Error if state doesn't match
            throw new LtiException("State not found");
        }
        return $this;
    }

    private function validateJwtFormat(): LtiMessageLaunch
    {
        $jwt = $this->request->get('id_token');

        if (empty($jwt)) {
            throw new LtiException("Missing id_token", 1);
        }

        // Get parts of JWT.
        $jwt_parts = explode('.', $jwt);

        if (count($jwt_parts) !== 3) {
            // Invalid number of parts in JWT.
            throw new LtiException("Invalid id_token, JWT must contain 3 parts", 1);
        }

        // Decode JWT headers.
        $this->jwt['header'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[0]), true);
        // Decode JWT Body.
        $this->jwt['body'] = json_decode(JWT::urlsafeB64Decode($jwt_parts[1]), true);

        return $this;
    }

    private function validateNonce(): LtiMessageLaunch
    {
        $nonce = Cache::get('nonce_' . $this->jwt['body']['nonce']);

        if (empty($nonce)) {
            throw new LtiException("Invalid Nonce");
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
            JWT::decode($this->request->get('id_token'), new Key($public_key['key'], 'RS256'));
        } catch (\Exception $e) {
            // Error validating signature.
            throw new LtiException("Invalid signature on id_token", 1);
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
            throw new LtiException("Invalid message type", 1);
        }

        $validators = [
            new DeepLinkMessageValidator(),
            new ResourceMessageValidator()
        ];

        $message_validator = false;
        foreach ($validators as $validator) {
            if ($validator->canValidate($this->jwt['body'])) {
                if ($message_validator !== false) {
                    // Can't have more than one validator apply at a time.
                    throw new LtiException("Validator conflict", 1);
                }
                $message_validator = $validator;
            }
        }

        if ($message_validator === false) {
            throw new LtiException("Unrecognized message type.", 1);
        }

        if (!$message_validator->validate($this->jwt['body'])) {
            throw new LtiException("Message validation failed.", 1);
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
