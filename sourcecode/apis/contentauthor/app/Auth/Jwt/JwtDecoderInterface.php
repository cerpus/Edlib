<?php

declare(strict_types=1);

namespace App\Auth\Jwt;

use stdClass;

interface JwtDecoderInterface
{
    /**
     * @throws JwtException if a verified payload could not be read
     */
    public function getVerifiedPayload(string $bearerToken): stdClass;
}
