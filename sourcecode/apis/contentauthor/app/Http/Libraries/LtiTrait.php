<?php

namespace App\Http\Libraries;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

trait LtiTrait
{
    public function ltiShow($id)
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->doShow($id, $ltiRequest->generateContextKey(), $ltiRequest->isPreview());
        } else {
            Log::error('[' . app('requestId') . '] ' . __METHOD__ . " Not a LTI request for showing H5P: $id.");
            Log::error([
                'requestId' => app('requestId'),
                'user' => Session::get('userId', 'not-logged-in-user'),
                'url' => request()->url(),
                'request' => request()->all()
            ]);
            throw new \Exception('No valid LTI request');
        }
    }

    public function ltiCreate(Request $request)
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->create($request);
        } else {
            Log::error(__METHOD__ . "Not a LTI request for H5P create.");
            Log::error(['user' => Session::get('userId', 'not-logged-in-user'), 'url' => request()->url(), 'request' => request()->all()]);
            throw new \Exception('No valid LTI request');
        }
    }

    public function ltiEdit(Request $request, $id)
    {
        $ltiRequest = $this->lti->getValidatedLtiRequest();
        if ($ltiRequest != null) {
            return $this->edit($request, $id);
        } else {
            Log::error(__METHOD__ . ": Not a LTI request for H5P: $id");
            Log::error(['user' => Session::get('userId', 'not-logged-in-user'), 'url' => request()->url(), 'request' => request()->all()]);
            throw new \Exception('No valid LTI request');
        }
    }
}
