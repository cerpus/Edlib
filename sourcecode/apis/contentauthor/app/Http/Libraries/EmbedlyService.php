<?php

namespace App\Http\Libraries;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmbedlyService
{
    public static function get($url)
    {
        if (empty($url)) {
            return null;
        }

        $cacheKey = 'embedly_response_' . sha1($url);

        if (Cache::has($cacheKey)) {
            $value = Cache::get($cacheKey);

            if (strlen($value) > 0) {
                $decoded = json_decode($value, true);
                if ($decoded != null) {
                    return $decoded;
                }
            }
        }

        $client = new Client();

        try {
            $response = $client->request('GET', "https://api.embedly.com/1/oembed", ["query" => [
                "url" => $url,
                "key" => config('embedly.key')
            ]]);

            $bodyRaw = $response->getBody()->getContents();

            Cache::put($cacheKey, $bodyRaw, now()->addDays(7));

            return json_decode($bodyRaw, true);
        } catch (ClientException $e) {
            Log::debug('Request to embedly failed with message: ' . $e->getMessage());
        }

        return null;
    }
}
