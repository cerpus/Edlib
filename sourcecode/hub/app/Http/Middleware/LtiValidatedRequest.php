<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Lti\Oauth1\LtiPlatformCredentialStore;
use App\Models\LtiTool;
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
    ) {}

    /**
     * @param (Closure(Request): Response) $next
     * @param string $context "platform" or "tool"
     */
    public function handle(Request $request, Closure $next, string $context): Response
    {
        switch ($context) {
            case 'platform':
                $credentialStore = $this->ltiPlatformCredentialStore;
                break;
            case 'tool':
                $tool = $request->route('tool');
                assert($tool instanceof LtiTool);

                $credentialStore = $tool->getOauth1Credentials();
                break;
            default:
                throw new LogicException('$context must be either "platform" or "tool"');
        }

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

        $request->attributes->set('lti', $oauthRequest->toArray());

        return $next($request);
    }
}
