<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Models\Content;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function abort;
use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * Get H5P info.
 *
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class ResourceInformationController
{
    public function __construct(private NdlaLegacyConfig $config) {}

    public function __invoke(Content $edlib2UsageContent): JsonResponse
    {
        $version = $edlib2UsageContent->latestVersion ?? abort(404);

        $caId = $this->config->extractH5pIdFromUrl($version->lti_launch_url);
        if ($caId === null) {
            abort(404, 'Not an H5P');
        }

        try {
            $json = $this->config
                ->getContentAuthorClient()
                ->request('GET', "h5p/$caId/info")
                ->getBody()
                ->getContents();
        } catch (ClientException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        $data = json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
        assert(is_array($data));

        return new JsonResponse([
            ...$data,

            // amend info from CA to have the hub's publish flag
            'published' => $version->published,
        ], headers: [
            'Content-Type' => 'application/json',
        ]);
    }
}
