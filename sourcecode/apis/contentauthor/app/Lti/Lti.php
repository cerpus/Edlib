<?php

declare(strict_types=1);

namespace App\Lti;

use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Cerpus\EdlibResourceKit\Oauth1\Exception\ValidationException;
use Cerpus\EdlibResourceKit\Oauth1\ValidatorInterface;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

use function array_map;

final readonly class Lti
{
    public function __construct(
        private ValidatorInterface $validator,
        private CredentialStoreInterface $credentialStore,
        private LoggerInterface $logger,
    ) {}

    /**
     * Get a validated LTI request from the Laravel request object.
     */
    public function getRequest(Request $request): LtiRequest|null
    {
        if ($request->attributes->has('lti_request')) {
            return $request->attributes->get('lti_request');
        }

        if (!$request->has('lti_message_type')) {
            return null;
        }

        // undo empty string => null conversion
        $params = array_map(fn($v) => $v === null ? '' : $v, $request->all());

        $ltiRequest = new LtiRequest($request->method(), $request->url(), $params);

        try {
            $this->validator->validate($ltiRequest, $this->credentialStore);

            $request->attributes->set('lti_request', $ltiRequest);

            return $ltiRequest;
        } catch (ValidationException $e) {
            $this->logger->warning('The request was not a valid OAuth 1.0 request', [
                'exception' => $e,
            ]);

            return null;
        }
    }
}
