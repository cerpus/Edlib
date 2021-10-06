<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Session;
use App\H5pLti;
use App\Http\Requests\LTIRequest;

class LtiLocale
{
    /**
     * Set locale based on lti param
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $ltiRequest = LTIRequest::current();
        if ($ltiRequest != null) {
            /** @var H5pLti $h5pLti */
            $h5pLti = app(H5pLti::class);
            if ($h5pLti->validatedLtiRequestOauth($ltiRequest)) {
                if ($ltiRequest->getLocale()) {
                    Session::put('locale', $ltiRequest->getLocale());
                }
            }
        }
        App::setLocale(Session::get('locale', config('app.fallback_locale')));

        return $next($request);
    }
}
