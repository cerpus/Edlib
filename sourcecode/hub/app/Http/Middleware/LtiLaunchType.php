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
        $requestedLaunchType = $request->session()->get('lti.lti_message_type');

        if (!is_string($requestedLaunchType)) {
            throw new LtiException('Invalid LTI launch type');
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
