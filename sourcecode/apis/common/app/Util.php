<?php

namespace App;

use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class Util
{
    /**
     * @throws \JsonException
     */
    public static function decodeResponse(
        ResponseInterface $response
    ): array|string|float|int|bool|null {
        $body = $response->getBody()->getContents();

        return \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);
    }
}
