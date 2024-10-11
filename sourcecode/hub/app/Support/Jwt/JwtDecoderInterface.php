<?php

declare(strict_types=1);

namespace App\Support\Jwt;

use stdClass;

interface JwtDecoderInterface
{
    /**
     * @throws JwtException
     */
    public function getVerifiedPayload(
        string $bearerToken,
        string $publicKeyOrJwksUri,
    ): stdClass|null;
}
