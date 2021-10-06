<?php

namespace App\Http\Middleware;

use Log;
use Closure;
use App\Http\Requests\Oauth10Request;

class SignedOauth10Request
{
    /**
     * Handle an incoming API request.
     * Verify Oauth 1.0 signature
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $logId = uniqid();
        try {
            $formPostContentType = "application/x-www-form-urlencoded";
            $contentType = $request->header('Content-Type', null);
            if (strlen($contentType) >= strlen($formPostContentType) && substr($contentType, 0, strlen($formPostContentType)) == $formPostContentType) {
                $params = $request->all();
            } else {
                $params = $request->query ? $request->query->all() : [];
            }
            $theRequest = new Oauth10Request($request->getMethod(), $request->url(), $params, $request->header('Authorization', null));
            $validRequest = $theRequest->validateOauth10(config('app.consumer-key'), config('app.consumer-secret'));
            if (!$validRequest) {
                Log::error("($logId) Unable to verify signature of oAuth 1.0 request to " . $request->url(),
                    $request->all());

                return response()->json([
                    'created' => false,
                    'log_id' => $logId,
                    'message' => "Invalid oAuth 1.0 signature. LogId: $logId"
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error("($logId) Exception verifying request to " . $request->url() . " :(" . $e->getCode() . ') ' . $e->getMessage(),
                $request->all());

            return response()->json([
                'created' => false,
                'log_id' => $logId,
                'message' => "Exception processing oauth10 request."
            ], 401);
        }

        return $next($request);
    }
}
