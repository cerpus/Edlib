<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Lti\Exception\Oauth1ValidationException;
use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1Validator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class LtiValidatedRequest
{
    public function __construct(private Oauth1Validator $oauth1Validator)
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

        // TODO: get credentials from database or something
        $credentials = new Oauth1Credentials('h5p', 'secret2');

        try {
            $this->oauth1Validator->validate($oauthRequest, $credentials);
        } catch (Oauth1ValidationException $e) {
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
