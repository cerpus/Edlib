<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1;

interface SignerInterface
{
    public function calculateSignature(
        Request $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
    ): string;

    /**
     * Get the signature method that will be used to calculate the signature of
     * a request.
     */
    public function getSignatureMethod(Request $request): string;

    public function sign(
        Request $request,
        Credentials $clientCredentials,
        Credentials|null $tokenCredentials = null,
    ): Request;
}
