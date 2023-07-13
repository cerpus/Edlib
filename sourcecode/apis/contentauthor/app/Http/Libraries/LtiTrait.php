<?php

namespace App\Http\Libraries;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * Classes using this trait should implement App\Http\Controllers\LtiTypeInterface
 */
trait LtiTrait
{
    public function ltiShow($id): string|View
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->doShow($id, $ltiRequest->generateContextKey(), $ltiRequest->isPreview());
        } else {
            Log::error(__METHOD__ . " Not a LTI request for showing H5P: $id.");
            Log::error(__METHOD__, [
                'user' => Session::get('userId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all()
            ]);
            throw new Exception('No valid LTI request');
        }
    }

    public function ltiCreate(Request $request): View
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->create($request);
        } else {
            Log::error(__METHOD__ . "Not a LTI request for H5P create.");
            Log::error(__METHOD__, ['user' => Session::get('userId', 'not-logged-in-user'), 'url' => request()->url(), 'request' => request()->all()]);
            throw new Exception('No valid LTI request');
        }
    }

    public function ltiEdit(Request $request, $id): View
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->edit($request, $id);
        } else {
            Log::error(__METHOD__ . ": Not a LTI request for H5P: $id");
            Log::error(__METHOD__, ['user' => Session::get('userId', 'not-logged-in-user'), 'url' => request()->url(), 'request' => request()->all()]);
            throw new Exception('No valid LTI request');
        }
    }
}
