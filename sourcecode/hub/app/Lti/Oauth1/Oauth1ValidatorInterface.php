<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Exception\Oauth1ValidationException;

interface Oauth1ValidatorInterface
{
    /**
     * Ensure an OAuth1 request is well-formed and authenticated.
     * @throws Oauth1ValidationException
     */
    public function validate(Oauth1Request $request): void;
}
