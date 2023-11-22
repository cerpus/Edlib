<?php

namespace App\Http\Libraries;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

trait LtiTrait
{
    public function ltiShow($id)
    {
        $ltiRequest = $this->lti->getRequest(request());

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        if (method_exists($this, 'doShow')) {
            return $this->doShow($id, $ltiRequest->generateContextKey(), $ltiRequest->isPreview());
        }

        abort(500, 'Requested action is not available');
    }

    public function ltiCreate(Request $request)
    {
        $ltiRequest = $this->lti->getRequest($request);

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        if (method_exists($this, 'create')) {
            return $this->create($request);
        }

        abort(500, 'Requested action is not available');
    }

    public function ltiEdit(Request $request, $id)
    {
        $ltiRequest = $this->lti->getRequest($request);

        if (!$ltiRequest) {
            throw new UnauthorizedHttpException(
                challenge: 'OAuth',
                message: 'No valid LTI request',
            );
        }

        if (method_exists($this, 'edit')) {
            return $this->edit($request, $id);
        }

        abort(500, 'Requested action is not available');
    }
}
