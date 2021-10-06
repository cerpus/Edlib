<?php

namespace App\Http\Middleware;

use App\Http\Requests\LTIRequest;
use Closure;
use Ramsey\Uuid\Uuid;

class CoreReturnUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->filled('return')) {
            $request->session()->put('returnUrl', $request->get('return'));
        } else {
            $ltiRequest = LTIRequest::current();
            if ($ltiRequest != null) {
                $returnUrl = $ltiRequest->getLaunchPresentationReturnUrl();
                if ($returnUrl) {
                    $listEntry = $request->get('redirectToken');
                    if (empty($listEntry)) {
                        $listEntry = Uuid::uuid4()->toString();
                        $request->request->add(['redirectToken' => $listEntry]);
                    }
                    $request->session()->put('list.returnUrls.' . $listEntry, $ltiRequest->getLaunchPresentationReturnUrl());
                    $request->session()->put("returnUrl", $returnUrl); //TODO Left for backward compabality when updating. Remove soon!
                }
            }
        }
        return $next($request);
    }
}
