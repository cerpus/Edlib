<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Get H5P copyright info.
 *
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class ResourceCopyrightController
{
    public function __construct(private NdlaLegacyConfig $config) {}

    public function __invoke(Content $edlib2UsageContent): JsonResponse
    {
        $launchUrl = $edlib2UsageContent->latestVersion?->lti_launch_url;
        assert($launchUrl !== null);

        $caId = $this->config->extractH5pIdFromUrl($launchUrl);
        if ($caId === null) {
            abort(404, 'Not an H5P');
        }

        try {
            $json = $this->config
                ->getContentAuthorClient()
                ->request('GET', "h5p/$caId/copyright")
                ->getBody()
                ->getContents();
        } catch (ClientException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $data = json_decode($json, flags: JSON_THROW_ON_ERROR);
        if (($data->h5p ?? null) === null) {
            abort(404, 'Missing H5P info');
        }

        return JsonResponse::fromJsonString($json, headers: [
            'Content-Type' => 'application/json',
        ]);
    }
}
