<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

interface Oauth1ValidatorInterface
{
    public function validate(
        Oauth1Request $request,
        Oauth1Credentials $credentials,
    ): bool;
}
