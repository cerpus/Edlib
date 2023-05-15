<?php

declare(strict_types=1);

namespace App\Lti\Oauth1;

interface Oauth1SignerInterface
{
    public function calculateSignature(
        Oauth1Request $request,
        Oauth1Credentials $clientCredentials,
        Oauth1Credentials|null $tokenCredentials = null,
    ): string;

    /**
     * Get the signature method that will be used to calculate the signature of
     * a request.
     */
    public function getSignatureMethod(Oauth1Request $request): string;

    public function sign(
        Oauth1Request $request,
        Oauth1Credentials $clientCredentials,
        Oauth1Credentials|null $tokenCredentials = null,
    ): Oauth1Request;
}
