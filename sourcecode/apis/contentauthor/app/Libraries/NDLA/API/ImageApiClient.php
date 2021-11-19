<?php

namespace App\Libraries\NDLA\API;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;

class ImageApiClient extends BaseNdlaApi
{
    public function fetchMetaData($imageId)
    {
        $cacheKey = 'NdlaImageApiMetadata|' . $imageId;
        $cacheTime = Carbon::now()->addHour();

        $imageMeta = Cache::get($cacheKey, null);

        if (!$imageMeta) {
            try {
                $path = sprintf('/image-api/v2/images/%s', $imageId);

                $response = $this->client->get($path, [
                    'headers' => [
                        'Accept-Language' => $this->language,
                    ],
                ]);

                $result = json_decode($response->getBody());

                if (json_last_error() === JSON_ERROR_NONE) {
                    $imageMeta = $result;
                    Cache::put($cacheKey, $imageMeta, $cacheTime);
                }
            } catch (ClientException $e) {

            }
        }

        return $imageMeta;
    }

    public function fetchImage($imageId)
    {
        if (!function_exists('fopen')) {
            throw new \Exception('Function fopen does not exist, aborting file download');
        }

        $image = null;

        $path = sprintf('/image-api/raw/id/%s', $imageId);

        $imageUri = config('ndla.api.uri') . $path;

        $image = fopen($imageUri, 'r');

        return $image;
    }
}
