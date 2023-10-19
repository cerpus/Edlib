<?php

namespace App;

use App\Http\Requests\LTIRequest;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Psr\Log\LoggerInterface;

class H5pLti
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly CredentialStoreInterface $credentialStore,
        private readonly LoggerInterface $logger,
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
        } catch (ValidationException $e) {
            $this->logger->warning('The request was not a valid OAuth 1.0 request', [
                'exception' => $e,
            ]);

            return null;
        }
    }
}
