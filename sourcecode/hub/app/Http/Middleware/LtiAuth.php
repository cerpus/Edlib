<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\LtiPlatform;
use Closure;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replace the login for the current request with credentials from the LTI
 * platform.
 */
final readonly class LtiAuth
{
    public function __construct(private AuthManager $authManager) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasPreviousSession()) {
            return $next($request);
        }

        $key = $request->session()->get('lti.oauth_consumer_key');
        $email = $request->session()->get('lti.lis_person_contact_email_primary');

        if ($key === null || $email === null) {
            return $next($request);
        }

        $platform = LtiPlatform::where('key', $key)->first();

        if ($platform?->enable_sso) {
            $this->authManager->onceUsingId($email);
        }

        return $next($request);
    }
}
