<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\LtiTool;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\Request as OAuthRequest;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class ToolAuthentication
{
    public function __construct(
        private ValidatorInterface $oauth1Validator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tool = LtiTool::firstWhere('consumer_key', $request->get('oauth_consumer_key'));
        if ($tool === null) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message:'OAuth 1.0 validation failure: Unknown consumer',
            );
        }

        try {
            $oauthRequest = new OAuthRequest(
                $request->method(),
                $request->url(), [
                    ...$request->query->all(),
                    ...$request->request->all(),
                ]
            );
            $credentials = $tool->getOauth1Credentials();

            $this->oauth1Validator->validate($oauthRequest, $credentials);
        } catch (ValidationException $e) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'OAuth 1.0 validation failure: ' . $e->getMessage(),
                previous: $e,
            );
        }

        return $next($request);
    }
}
