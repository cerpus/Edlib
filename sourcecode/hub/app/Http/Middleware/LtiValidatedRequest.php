<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class LtiValidatedRequest
{
    public function __construct(private ValidatorInterface $oauth1Validator)
    {
    }

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $oauthRequest = new Oauth1Request($request->method(), $request->url(), [
            ...$request->query->all(),
            ...$request->request->all(),
        ]);

        try {
            $this->oauth1Validator->validate($oauthRequest);
        } catch (ValidationException $e) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'OAuth 1.0 validation failure: ' . $e->getMessage(),
                previous: $e,
            );
        }

        $request->session()->put('lti', $oauthRequest->toArray());

        return $next($request);
    }
}
