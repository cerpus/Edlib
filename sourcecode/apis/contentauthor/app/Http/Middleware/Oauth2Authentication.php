<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 22.07.16
 * Time: 11:28
 */

namespace App\Http\Middleware;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class Oauth2Authentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->bearerToken();
        if ($accessToken === null) {
            $accessToken = $request->input('access_token');
            if (!$accessToken) {
                Log::error(__METHOD__.": Token not found in header or query params.");

                return response('Unauthorized.', Response::HTTP_UNAUTHORIZED);
            }
        }
        $authService = config('cerpus-auth.server', null);
        if (substr($authService, -1) != "/") {
            $authService .= "/";
        }
        $checkTokenUri = "v1/oauth/check_role?role=ROLE_LICENSING"; //TODO Add separate role in Auth
        $client = new Client(['base_uri' => $authService]);
        try {
            $response = $client->get($checkTokenUri, [
                'allow_redirects' => false,
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                ],
            ]);
            if ($response->getStatusCode() == Response::HTTP_OK) {
                return $next($request);
            } else {
                Log::error(__METHOD__.": Token is missing role: ROLE_LICENSING");

                return response('Forbidden', Response::HTTP_FORBIDDEN);
            }
        } catch (ClientException $e) {
            Log::error(__METHOD__.": Validating token failed. ({$e->getCode()}) {$e->getMessage()}");

            return response('Forbidden', Response::HTTP_FORBIDDEN);
        }
    }
}
