<?php

namespace App;

use App\Exceptions\NotFoundException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
class Util
{
    /**
     * @return array|string|float|int|bool|null
     * @throws \JsonException
     */
    public static function decodeResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();

        return \json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param callable():ResponseInterface $wrapper
     * @return array|string|float|int|bool|null
     * @throws NotFoundException
     */
    public static function handleEdlibNodeApiRequest(callable $wrapper)
    {
        try {
            $response = $wrapper();
            return self::decodeResponse($response);
        } catch (RequestException $e) {
            $response = $e->getResponse();

            if (!$response || $response->getStatusCode() !== 404) {
                throw $e;
            }

            try {
                $data = Util::decodeResponse($response);
            } catch (\JsonException $jsonException) {
                throw $e;
            }

            $field = $data["error"]["parameter"] ?? null;
            throw new NotFoundException($field);
        }
    }
}
