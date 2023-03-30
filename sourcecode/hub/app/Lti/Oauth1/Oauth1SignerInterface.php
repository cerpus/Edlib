<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

interface Oauth1SignerInterface
{
    public function sign(
        Oauth1Request $request,
        Oauth1Credentials $credentials
    ): Oauth1Request;
}
