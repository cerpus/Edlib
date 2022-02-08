<?php

namespace App\Http\Middleware;

use App\Http\Libraries\AuthJwtParser;
use Cerpus\LaravelAuth\Service\JWTValidationService;
use Closure;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class EdlibParseJwt extends AuthJwtParser
{
    private function getJwtFromRequest(Request $request): ?string
    {
        $authorize = trim($request->header('Authorization', ''));
        $prefix = 'Bearer';

        if (str_starts_with($authorize, $prefix)) {
            $token = trim(substr($authorize, strlen($prefix)));
            if (!empty($token)) {
                return $token;
            }
        }

        if ($request->has('jwt')) {
            return $request->get('jwt');
        }

        return null;
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
        $jwtService = new JWTValidationService();
        $jwt = $this->getJwtFromRequest($request);

        if ($jwt != null) {
            $validJwt = $jwtService->validateJwt($jwt);
            if ($validJwt !== null && $validJwt->getType() == 'edlib') {
                $payload = $validJwt->getPayload();
                Session::put('authId', $payload->sub);
                Session::put('userId', $payload->sub);
                $user = $payload->payload->user;
                $roles = $payload->payload->roles;
                Session::put('name', $this->getBestName($user));
                Session::put('email', $this->getEmail($user));
                Session::put('verifiedEmails', $this->getVerifiedEmails($user));
                Session::put('isAdmin', in_array('superadmin', $roles));
                Session::put('roles', $roles);
                $genericUser = new GenericUser([
                    'id' => $payload->sub,
                    'name' => $this->getBestName($user),
                    'email' => $this->getEmail($user)
                ]);
                Session::put('user', $genericUser);
                Auth::login($genericUser);
                return $next($request);
            }
        }

        $isLoggedIn = Session::get('authId');

        if ($isLoggedIn) {
            return $next($request);
        }

        return redirect('auth/login');
    }
}
