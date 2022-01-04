<?php


namespace App\Services\Lti;

use App\Exceptions\LtiException;
use App\Models\LtiRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LtiOIDCLogin
{
    public function __construct(private LtiRegistration $registration)
    {
    }

    /**
     * @throws LtiException
     */
    public static function doLogin(LtiRegistration $registration, string $launch_url, array $request = null): RedirectResponse
    {
        $oidcLogin = new LtiOIDCLogin($registration);

        return $oidcLogin->doOIDCLoginRedirect($launch_url, $request);
    }

    /**
     * Calculate the redirect location to return to based on an OIDC third party initiated login request.
     *
     * @throws LtiException
     */
    public function doOIDCLoginRedirect(string $launch_url, array $request = null): RedirectResponse
    {
        if (empty($launch_url) || empty($request)) {
            throw new LtiException('No launch URL configured');
        }

        // Validate Request Data.
        $this->validateLogin($request);

        // Generate State.
        // Set cookie (short lived)
        $state = str_replace('.', '_', uniqid('state-', true));

        // Generate Nonce.
        $nonce = Str::uuid()->toString();
        Cache::put('nonce_' . $nonce, $nonce, $seconds = 60 * 60);

        // Build Response.
        $auth_params = [
            'scope' => 'openid', // OIDC Scope.
            'response_type' => 'id_token', // OIDC response is always an id token.
            'response_mode' => 'form_post', // OIDC response is always a form post.
            'prompt' => 'none', // Don't prompt user on redirect.
            'client_id' => $this->registration->client_id, // Registered client id.
            'redirect_uri' => $launch_url, // URL to return to after login.
            'state' => $state, // State to identify browser session.
            'nonce' => $nonce, // Prevent replay attacks.
            'login_hint' => $request['login_hint'] // Login hint to identify platform session.
        ];

        // Pass back LTI message hint if we have it.
        if (isset($request['lti_message_hint'])) {
            // LTI message hint to identify LTI context within the platform.
            $auth_params['lti_message_hint'] = $request['lti_message_hint'];
        }

        return redirect($this->registration->platform_login_auth_endpoint . "?" . http_build_query($auth_params))->cookie(
            "lti1p3_$state",
            $state,
            1,
            '/',
            null,
            null,
            true,
            false,
            'None'
        );
    }

    /**
     * @throws LtiException
     */
    protected function validateLogin($request): void
    {
        if (empty($request['iss'])) {
            throw new LtiException("Could not find issuer");
        }

        if (empty($request['login_hint'])) {
            throw new LtiException("Could not find login hint");
        }

        if (empty($this->registration) || $this->registration->issuer !== $request['iss']) {
            throw new LtiException("Could not find registration details");
        }
    }
}
