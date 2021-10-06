<?php


namespace App\Http\Middleware;


use App\Http\Requests\LTIRequest;
use App\SessionKeys;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DraftAction
{
    public function handle(Request $request, Closure $next)
    {
        $ltiRequest = $this->getLtiRequest();
        if ($ltiRequest && $ltiRequest->getExtUseDraftLogic() && $request->hasSession()) {
            $listEntry = $request->get('redirectToken');
            if (empty($listEntry)) {
                $listEntry = Str::uuid();
                $request->request->add(['redirectToken' => $listEntry]);
            }

            $draftEnabled = filter_var($ltiRequest->getExtUseDraftLogic(), FILTER_VALIDATE_BOOLEAN);
            $request->session()->put(sprintf(SessionKeys::EXT_DRAFT_SETTING, $listEntry), $draftEnabled);
        }

        return $next($request);
    }

    protected function getLtiRequest()
    {
        return LTIRequest::current();
    }
}