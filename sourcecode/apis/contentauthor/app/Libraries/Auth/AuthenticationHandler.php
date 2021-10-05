<?php

namespace App\Libraries\Auth;


use Cerpus\AuthCore\IdentityResponse;
use Cerpus\AuthCore\Oauth2Flow;
use Cerpus\AuthCore\TokenResponse;
use Illuminate\Support\Facades\Session;

class AuthenticationHandler implements \Cerpus\AuthCore\AuthenticationHandler, ContentAuthorAuthenticationHandler {
    /**
     * Called before the token is put into the TokenManager. Return to proceed with adding the token to the TokenManager and then calling afterTokenAvailability.
     *
     * @param \Cerpus\AuthCore\TokenResponse $tokenResponse
     * @param \Cerpus\AuthCore\IdentityResponse $identityResponse
     *
     * @return bool Return true to proceed with login.
     */
    public function beforeTokenAvailability(
        Oauth2Flow $flow,
        TokenResponse $tokenResponse,
        IdentityResponse $identityResponse
    ): bool {
        return true;
    }

    /**
     * Called after putting the token into the TokenManager
     *
     * @param \Cerpus\AuthCore\Oauth2Flow $flow
     * @param \Cerpus\AuthCore\IdentityResponse $identityResponse
     */
    public function afterTokenAvailability(
        Oauth2Flow $flow,
        IdentityResponse $identityResponse
    ) {
        $userId = $identityResponse->identityId;
        Session::put('authId', $userId);
        Session::put('name', $this->getBestName($identityResponse));
        Session::put('email', $this->getEmail($identityResponse));
        Session::put('verifiedEmails', $this->getVerifiedEmails($identityResponse));
        Session::put('authAdmin', $identityResponse->admin);

        $returnTo = $flow->getSuccessUrl();
        if (is_null($returnTo)) { // return address lost...
            $returnTo = url('/create');
        }

        return redirect($returnTo);
    }

    /**
     * Authorization failed.
     *
     * @param \Cerpus\AuthCore\Oauth2Flow $flow
     * @param mixed|null $error
     *
     * @return mixed
     */
    public function failed(Oauth2Flow $flow, $error = NULL) {
        throw new \Exception('Could not get access token.');
    }

    protected function getBestName(IdentityResponse $identityResponse)
    {
        $name = 'noname';
        if ($identityResponse->displayName) {
            $name = $identityResponse->displayName;
        } else if ($identityResponse->firstName || $identityResponse->lastName) {
            $names = [];
            if ($identityResponse->firstName) {
                $names[] = $identityResponse->firstName;
            }
            if ($identityResponse->lastName) {
                $names[] = $identityResponse->lastName;
            }
            $name = trim(implode(' ', $names));
        }

        return $name;
    }

    protected function getEmail(IdentityResponse $identityResponse)
    {
        // Do not enter unverified emails into Session
        $email = 'noemail';
        if ($identityResponse->email) {
            $email = $identityResponse->email;
        }

        return $email;
    }

    protected function getVerifiedEmails(IdentityResponse $identityResponse)
    {
        $verifiedEmails = [];

        if ($identityResponse->email) {
            $verifiedEmails[] = strtolower($identityResponse->email); // Add primary email
        }

        foreach ($identityResponse->additionalEmails as $email) {
            $verifiedEmails[] = strtolower($email);
        }

        return array_unique($verifiedEmails);
    }

    public function perRequestAuthentication(IdentityResponse $identityResponse) {
        $userId = $identityResponse->identityId;
        Session::put('authId', $userId);
        Session::put('name', $this->getBestName($identityResponse));
        Session::put('email', $this->getEmail($identityResponse));
        Session::put('verifiedEmails', $this->getVerifiedEmails($identityResponse));
        Session::put('authAdmin', $identityResponse->admin);
    }
}
