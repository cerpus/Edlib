<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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
    public function __construct(private AuthManager $authManager)
    {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TODO: apply this conditionally based on LTI platform setting.
        // For now, we trust that the LTI platform won't lie about the user.
        if ($request->hasPreviousSession()) {
            $email = $request->session()->get('lti.lis_person_contact_email_primary');

            $this->authManager->onceUsingId($email);
        }

        return $next($request);
    }
}
