<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\SessionScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class StartScopedLtiSession
{
    public function __construct(private SessionScope $scope) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ltiData = $request->attributes->get('lti');
        assert(is_array($ltiData));

        $session = $this->scope->start($request);
        $session->put('lti', $ltiData);

        return $next($request);
    }
}
