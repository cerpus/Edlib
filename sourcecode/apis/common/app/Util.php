<?php

namespace App;

use App\Exceptions\NotFoundException;
use App\Exceptions\UnauthorizedException;
use GuzzleHttp\Exception\RequestException;
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
    ): array|string|float|int|bool|null
    {
        $body = $response->getBody()->getContents();

        return \json_decode($body, true, flags: \JSON_THROW_ON_ERROR);
    }

    /**
     * @throws NotFoundException
     * @throws \JsonException
     * @throws UnauthorizedException
     */
    public static function handleEdlibNodeApiRequest(callable $wrapper)
    {
        try {
            $response = $wrapper();
            return self::decodeResponse($response);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            try {
                $data = Util::decodeResponse($e->getResponse());
            } catch (\JsonException $jsonException) {
                throw $e;
            }

            if ($statusCode == 404) {
                $field = $data["error"]["parameter"] ?? null;
                throw new NotFoundException($field);
            }

            if ($statusCode == 401) {
                throw new UnauthorizedException();
            }

            throw $e;
        }
    }
}
