<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\LtiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function is_string;

/**
 * Enforce a specific LTI launch type.
 */
final readonly class LtiLaunchType
{
    /**
     * @throws LtiException
     */
    public function handle(Request $request, Closure $next, string $launchType): Response
    {
        $requestedLaunchType = $request->attributes->get('lti')['lti_message_type'] ?? null;

        if (!is_string($requestedLaunchType)) {
            throw new LtiException('LTI launch type is not stored in request');
        }

        if ($requestedLaunchType !== $launchType) {
            throw new LtiException(sprintf(
                'Invalid LTI launch type, expected "%s" but got "%s"',
                $launchType,
                $requestedLaunchType,
            ));
        }

        return $next($request);
    }
}
