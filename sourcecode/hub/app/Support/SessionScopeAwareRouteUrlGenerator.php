<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Routing\RouteUrlGenerator;
use function array_key_exists;

/**
 * @see \App\Http\Middleware\ScopedSession
 */
final class SessionScopeAwareRouteUrlGenerator extends RouteUrlGenerator
{
    /**
     * @param array<string, string> $parameters
     */
    public function to($route, $parameters = [], $absolute = false): string
    {
        $sessionScope = app()->make(SessionScope::class);

        if (
            !array_key_exists(SessionScope::TOKEN_PARAM, $parameters) &&
            $sessionScope->isScoped($this->request)
        ) {
            $token = $sessionScope->getToken($this->request);
            assert($token !== null);

            $parameters[SessionScope::TOKEN_PARAM] = $token;
        }

        return parent::to($route, $parameters, $absolute);
    }
}
