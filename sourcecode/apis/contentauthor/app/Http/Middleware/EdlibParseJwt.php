<?php

namespace App\Http\Middleware;

use App\Auth\Jwt\JwtDecoderInterface;
use App\Http\Libraries\AuthJwtParser;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EdlibParseJwt extends AuthJwtParser
{
    public function __construct(private readonly JwtDecoderInterface $jwtReader)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $bearerToken = $request->bearerToken() ?? $request->get('jwt');

        if ($bearerToken !== null) {
            $payload = $this->jwtReader->getVerifiedPayload($bearerToken);
            Session::put('authId', $payload->sub);
            Session::put('userId', $payload->sub);
            $user = $payload->payload->user;
            $roles = $payload->payload->roles ?? [];
            Session::put('name', $this->getBestName($user));
            Session::put('email', $this->getEmail($user));
            Session::put('verifiedEmails', $this->getVerifiedEmails($user));
            Session::put('isAdmin', in_array('superadmin', $roles));
            Session::put('roles', $roles);
            Auth::login(new GenericUser([
                'id' => $payload->sub,
                'name' => $this->getBestName($user),
                'email' => $this->getEmail($user)
            ]));
            return $next($request);
        }

        $isLoggedIn = Session::get('authId');

        if ($isLoggedIn) {
            return $next($request);
        }

        return redirect('auth/login');
    }
}
