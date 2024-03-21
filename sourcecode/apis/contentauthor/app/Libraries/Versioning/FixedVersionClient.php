<?php

declare(strict_types=1);

namespace App\Libraries\Versioning;

use BadMethodCallException;
use Cerpus\VersionClient\interfaces\VersionClientInterface;
use Cerpus\VersionClient\interfaces\VersionDataInterface;
use Cerpus\VersionClient\VersionData;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

use function json_decode;
use function property_exists;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final readonly class FixedVersionClient implements VersionClientInterface
{
    public function __construct(private string $versionServer)
    {
    }

    /**
     * Get version info
     *
     * @throws GuzzleException
     * @throws JsonException
     * @throws Exception
     */
    public function getVersion($versionId): VersionData
    {
        $responseBody = $this->getClient()
            ->request('GET', sprintf("/v1/resources/%s", $versionId))
            ->getBody()
            ->getContents();

        $data = json_decode($responseBody, flags: JSON_THROW_ON_ERROR, depth: 4096);
        $this->verifyResponse($data);

        return (new VersionData())->populate($data->data);
    }

    public function createVersion(VersionDataInterface $versionData): never
    {
        throw new BadMethodCallException('not implemented');
    }

    public function getVersionId()
    {
        throw new BadMethodCallException('not implemented');
    }

    /**
     * @throws Exception
     */
    private function verifyResponse(object $data): void
    {
        $valid = property_exists($data, 'data')
            && property_exists($data, 'errors')
            && property_exists($data, 'type')
            && property_exists($data, 'message');

        if (!$valid) {
            throw new Exception('Invalid data format in response');
        }
    }

    private function getClient(): Client
    {
        return new Client(['base_uri' => $this->versionServer]);
    }
}
