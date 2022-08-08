<?php

namespace App\Http\Middleware;

use App\Auth\Jwt\JwtDecoderInterface;
use App\Http\Libraries\AuthJwtParser;
use Closure;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InternalHandleJwt extends AuthJwtParser
{
    public function __construct(
        private readonly JwtDecoderInterface $jwtDecoder,
    ) {
    }

    /*
     * Extract Behavior settings from a LTI request, validate and add to Session if valid
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('jwt')) {
            return response("Unauthorized", ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $data = JWT::decode($request->get('jwt'), new Key(config('internal.toolKey'), 'HS256'));

        $payload = $this->jwtDecoder->getVerifiedPayload($data->userToken);

        $request->merge((array) $data);
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
