<?php

namespace App\Http\Middleware;

use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\Request as Oauth10Request;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Verifies the validity of an OAuth 1.0 request.
 */
final readonly class SignedOauth10Request
{
    public function __construct(
        private ValidatorInterface $validator,
        private CredentialStoreInterface $credentialStore,
    ) {}

    /**
     * @param (Closure(Request): Response) $next
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $contentType = $request->header('Content-Type', '');
        if (str_starts_with($contentType, "application/x-www-form-urlencoded")) {
            $params = $request->all();
        } else {
            $params = $request->query->all();
        }

        $oauth1Request = new Oauth10Request(
            $request->getMethod(),
            $request->url(),
            // undo empty string => null conversion
            array_map(fn($v) => $v === null ? '' : $v, $params),
        );

        try {
            $this->validator->validate($oauth1Request, $this->credentialStore);
        } catch (ValidationException $e) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: "Unable to verify signature of OAuth 1.0 request to " . $request->url(),
                previous: $e,
            );
        }

        return $next($request);
    }
}
