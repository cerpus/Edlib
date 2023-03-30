<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1Request;
use App\Lti\Oauth1\Oauth1Validator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class LtiValidatedRequest
{
    public function __construct(private Oauth1Validator $validator)
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

        $credentials = new Oauth1Credentials('h5p', 'secret2');

        if (!$this->validator->validate($oauthRequest, $credentials)) {
            throw new UnauthorizedHttpException(challenge: 'OAuth');
        }

        return $next($request);
    }
}
