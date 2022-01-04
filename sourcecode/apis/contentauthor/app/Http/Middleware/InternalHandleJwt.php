<?php

namespace App\Http\Middleware;

use App\Http\Libraries\AuthJwtParser;
use Cerpus\LaravelAuth\Service\JWTValidationService;
use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InternalHandleJwt extends AuthJwtParser
{
    /*
     * Extract Behavior settings from a LTI request, validate and add to Session if valid
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('jwt')) {
            return response("Unauthorized", ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $data = JWT::decode($request->get('jwt'), config('internal.toolKey'), ['HS256']);

        $jwtService = new JWTValidationService();
        $validJwt = $jwtService->validateJwt($data->userToken);

        if ($validJwt === null || $validJwt->getType() !== 'edlib') {
            return response("Unauthorized", ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $request->merge((array) $data);

        $payload = $validJwt->getPayload();
        $authId = $payload->sub;
        Session::put('authId', $authId);
        $user = $payload->payload->user;
        Session::put('name', $this->getBestName($user, null));
        Session::put('email', $this->getEmail($user, null));
        Session::put('verifiedEmails', $this->getVerifiedEmails($user));
        Session::put('isAdmin', $user->isAdmin === 1);

        return $next($request);
    }
}
