<?php

namespace App\Http\Middleware;

use App\Http\Libraries\License;
use App\Lti\Lti;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Add LTI parameters to session
 */
final readonly class LtiAddToSession
{
    public function __construct(private Lti $lti) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ltiRequest = $this->lti->getRequest($request);

        if ($ltiRequest != null) {
            Session::put('signedLaunch', true);
            Session::put('userId', $ltiRequest->getUserId());

            if ($ltiRequest->getUserId() != null) {
                Session::put('authId', $ltiRequest->getUserId());
                Session::put('name', $ltiRequest->getUserName());
                Session::put('email', $ltiRequest->getUserEmail() ?? 'noemail');
                Session::put('isAdmin', $ltiRequest->isAdministrator());
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
