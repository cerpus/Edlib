<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Oauth1\LtiPlatformCredentialStore;
use App\Oauth1\LtiToolCredentialStore;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth1Request;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Closure;
use Illuminate\Http\Request;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final readonly class LtiValidatedRequest
{
    public function __construct(
        private ValidatorInterface $oauth1Validator,
        private LtiPlatformCredentialStore $ltiPlatformCredentialStore,
        private LtiToolCredentialStore $ltiToolCredentialStore,
    ) {
    }

    /**
     * @param (Closure(Request): Response) $next
     * @param string $context "platform" or "tool"
     */
    public function handle(Request $request, Closure $next, string $context): Response
    {
        $credentialStore = match ($context) {
            'platform' => $this->ltiPlatformCredentialStore,
            'tool' => $this->ltiToolCredentialStore,
            default => throw new LogicException('$context must be either "platform" or "tool"'),
        };

        $oauthRequest = new Oauth1Request($request->method(), $request->url(), [
            ...$request->query->all(),
            ...$request->request->all(),
        ]);

        try {
            $this->oauth1Validator->validate($oauthRequest, $credentialStore);
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
