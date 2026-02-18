<?php

namespace App\Http\Middleware;

use App\Libraries\OAuth1\BodyHashRequest;
use App\Libraries\OAuth1\BodyHashValidator;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

// Verify that message is signed using OAuth body signing
readonly class OAuth1BodySigned
{
    public function __construct(
        private BodyHashValidator $validator,
        private CredentialStoreInterface $credentialStore,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        try {
            $oauthRequest = new BodyHashRequest(
                $request->method(),
                $request->url(),
                $request->getContent(),
                $request->headers,
            );

            $this->validator->validate(
                $oauthRequest,
                $this->credentialStore
            );
        } catch (ValidationException $e) {
            Log::info(__METHOD__ . ': Failed to validate OAuth1 message', [$e->getMessage()]);
            throw new BadRequestHttpException($e->getMessage());
        }

        return $next($request);
    }
}
