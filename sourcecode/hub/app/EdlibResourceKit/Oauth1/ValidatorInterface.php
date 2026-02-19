<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

use App\EdlibResourceKit\Oauth1\Exception\ValidationException;

interface ValidatorInterface
{
    /**
     * Ensure an OAuth1 request is well-formed and authenticated.
     * @throws ValidationException
     */
    public function validate(
        Request $request,
        CredentialStoreInterface $credentialStore,
    ): void;
}
