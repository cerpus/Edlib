<?php

namespace App\Http\Middleware;

use App\Auth\Jwt\JwtDecoderInterface;
use App\Auth\Jwt\JwtException;
use App\H5pLti;
use App\Http\Libraries\License;
use Closure;
use Illuminate\Support\Facades\Session;

class LtiRequestAuth
{
    public function __construct(
        private readonly H5pLti $h5pLti,
        private readonly JwtDecoderInterface $jwtDecoder,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     */
    public function handle($request, Closure $next)
    {
        $ltiRequest = $this->h5pLti->getValidatedLtiRequest();

        if ($ltiRequest != null) {
            Session::put('userId', $ltiRequest->getUserId());
            if ($ltiRequest->getExtUserId() != null) {
                Session::put('authId', $ltiRequest->getExtUserId());
                if ($ltiRequest->getUserGivenName() != null && $ltiRequest->getUserFamilyName() != null) {
                    Session::put('name', $ltiRequest->getUserGivenName().' '.$ltiRequest->getUserFamilyName());
                }
                Session::put('email', $ltiRequest->getUserEmail() ?? 'noemail');
            }
            $jwt = $ltiRequest->getExtJwtToken();
            if ($jwt !== null && $jwt !== '') {
                try {
                    $this->jwtDecoder->getVerifiedPayload($jwt);
                    Session::put('jwtToken', ['raw' => $jwt]);
                } catch (JwtException) {
                    Session::remove('jwtToken');
                }
            } else {
                Session::remove('jwtToken');
            }
            $allowedLicenses = implode(',', [
                License::LICENSE_PRIVATE,
                License::LICENSE_CC0,
                License::LICENSE_BY,
                License::LICENSE_BY_SA,
                License::LICENSE_BY_NC,
                License::LICENSE_BY_ND,
                License::LICENSE_BY_NC_SA,
                License::LICENSE_BY_NC_ND,
                License::LICENSE_PDM,
                License::LICENSE_EDLIB,
            ]);
            Session::put('allowedLicenses', $ltiRequest->getAllowedLicenses($allowedLicenses));
            $defaultLicense = License::LICENSE_BY;
            Session::put('defaultLicense', $ltiRequest->getDefaultLicense($defaultLicense));
            Session::put('originalSystem', $ltiRequest->getToolConsumerInfoProductFamilyCode());
        }
        return $next($request);
    }
}
