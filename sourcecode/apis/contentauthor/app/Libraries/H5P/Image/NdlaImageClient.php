<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Image;

use GuzzleHttp\Client;
use GuzzleHttp\ClientTrait;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class NdlaImageClient
{
    use ClientTrait;

    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client($config);
    }


    /**
     * @throws \GuzzleHttp\Exception\RequestException on HTTP request errors (4xx, 5xx responses)
     * @throws \GuzzleHttp\Exception\ConnectException on network connection failures
     * @throws \GuzzleHttp\Exception\TransferException on transfer errors
     */
    public function request(string $method, $uri, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $uri, $options);
    }

    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        return $this->client->requestAsync($method, $uri, $options);
    }
}
