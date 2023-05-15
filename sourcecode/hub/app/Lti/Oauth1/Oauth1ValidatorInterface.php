<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

use App\Lti\Exception\Oauth1ValidationException;

interface Oauth1ValidatorInterface
{
    /**
     * @throws Oauth1ValidationException
     */
    public function validate(
        Oauth1Request $request,
        Oauth1Credentials $consumerCredentials,
    ): void;
}
