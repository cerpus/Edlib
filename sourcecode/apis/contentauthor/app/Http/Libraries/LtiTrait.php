<?php

namespace App\Http\Libraries;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait LtiTrait
{
    public function ltiShow($id)
    {
        if (!method_exists($this, 'doShow')) {
            abort(404, 'Requested action is not available');
        }

        $ltiRequest = $this->lti->getRequest(request());

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        return $this->doShow($id, $ltiRequest->generateContextKey(), $ltiRequest->isPreview());
    }

    public function ltiCreate(Request $request)
    {
        if (!method_exists($this, 'create')) {
            abort(404, 'Requested action is not available');
        }

        $ltiRequest = $this->lti->getRequest($request);

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        return $this->create($request);
    }

    public function ltiEdit(Request $request, $id)
    {
        if (!method_exists($this, 'edit')) {
            abort(404, 'Requested action is not available');
        }

        $ltiRequest = $this->lti->getRequest($request);

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        return $this->edit($request, $id);
    }
}
