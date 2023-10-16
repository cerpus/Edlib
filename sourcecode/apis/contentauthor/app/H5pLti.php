<?php

namespace App;

use App\Http\Requests\LTIRequest;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;

class H5pLti
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CredentialStoreInterface $credentialStore,
    ) {
    }

    public function getValidatedLtiRequest(): LTIRequest|null
    {
        $ltiRequest = LTIRequest::fromRequest(request());

        if (!$ltiRequest) {
            return null;
        }

        try {
            $this->validator->validate($ltiRequest, $this->credentialStore);

            return $ltiRequest;
        } catch (ValidationException) {
            return null;
        }
    }
}
