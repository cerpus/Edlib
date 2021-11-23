<?php

namespace App;

use App\Exceptions\NotFoundException;
use Exception;
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

            throw $e;
        }
    }

    /**
     * @throws NotFoundException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public static function handleEdlibNodeApiLaravelRequest(callable $wrapper)
    {
        try {
            return $wrapper()->throw()->json();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $statusCode = $e->response->status();
            try {
                $data = $e->response->json();
            } catch (Exception $nested) {
                throw $e;
            }

            if ($statusCode == 404) {
                $field = $data["error"]["parameter"] ?? null;
                throw new NotFoundException($field);
            }

            throw $e;
        }
    }
}
