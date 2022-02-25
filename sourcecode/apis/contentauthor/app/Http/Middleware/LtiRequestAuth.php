<?php

namespace App\Http\Middleware;

use App\H5pLti;
use App\Http\Libraries\License;
use App\Http\Requests\LTIRequest;
use Cerpus\LaravelAuth\Service\JWTValidationService;
use Closure;
use Illuminate\Support\Facades\Session;

class LtiRequestAuth {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        /** @var LTIRequest $ltiRequest */
        $ltiRequest = LTIRequest::current();
        if ($ltiRequest != null) {
            /** @var H5pLti $h5pLti */
            $h5pLti = app(H5pLti::class);
            if ($h5pLti->validatedLtiRequestOauth($ltiRequest)) {
                Session::put('userId', $ltiRequest->getUserId());
                if ($ltiRequest->getExtUserId() != null) {
                    Session::put('authId', $ltiRequest->getExtUserId());
                    if ($ltiRequest->getUserGivenName() != null && $ltiRequest->getUserFamilyName() != null) {
                        Session::put('name', $ltiRequest->getUserGivenName().' '.$ltiRequest->getUserFamilyName());
                    }
                    Session::put('email', $ltiRequest->getUserEmail(), 'noemail');
                }
                $jwtService = app(JWTValidationService::class);
                $jwt = $ltiRequest->getExtJwtToken();
                $validJwt = $jwt ? $jwtService->validateJwt($jwt) : null;
                if ($validJwt) {
                    Session::put('jwtToken', [
                        'context' => $validJwt->getContextName(),
                        'raw' => $validJwt->toString(),
                        'payload' => $validJwt->getPayload()
                    ]);
                } else {
                    Session::put('jwtToken', null);
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
        }
        return $next($request);
    }
}
