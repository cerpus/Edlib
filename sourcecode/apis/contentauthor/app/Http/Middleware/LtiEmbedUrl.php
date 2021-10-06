<?php

namespace App\Http\Middleware;

use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LtiEmbedUrl
{
    public function handle(Request $request, \Closure $next)
    {
        $ltiRequest = LTIRequest::current();

        if ($ltiRequest && $ltiRequest->getExtContextOembed()) {
            $embedCode = sprintf(
                '<iframe src="%s" width=":w" height=":h"></iframe>',
                htmlspecialchars($ltiRequest->getExtContextOembed(), ENT_QUOTES, 'UTF-8')
            );

            Session::flash(SessionKeys::EXT_CONTEXT_EMBED, $embedCode);
        }

        return $next($request);
    }
}
