<?php

namespace App\Libraries\NDLA\API;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;

class AudioApiClient extends BaseNdlaApi
{
    public function fetchMetaData($audioId)
    {
        $cacheKey = 'NdlaAudioApiMetadata|' . $audioId;
        $cacheTime = Carbon::now()->addHour();

        if (!$audioMeta = Cache::get($cacheKey, null)) {
            try {
                $path = sprintf('/audio-api/v1/audio/%s', $audioId);

                $response = $this->client->get($path, [
                    'headers' => [
                        'Accept-Language' => $this->language,
                    ],
                ]);

                $result = json_decode($response->getBody());

                if (json_last_error() === JSON_ERROR_NONE) {
                    $audioMeta = $result;
                    Cache::put($cacheKey, $audioMeta, $cacheTime);
                }
            } catch (ClientException $e) {

            }
        }

        return $audioMeta;
    }
}
