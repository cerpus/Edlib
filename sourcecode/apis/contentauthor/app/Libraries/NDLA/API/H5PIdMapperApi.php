<?php

namespace App\Libraries\NDLA\API;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class H5PIdMapperApi
{
    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client;
        if (!$client) {
            $this->client = new Client([
                'base_uri' => 'https://h5p.ndla.no'
            ]);
        }
    }

    public function fetchOldH5PNodeId($newId)
    {
        $cacheKey = "NDLAImportOldH5PId|$newId";
        $cacheTime = Carbon::now()->addHours(12);

        if (!$oldId = Cache::get($cacheKey, null)) {
            try {
                $path = sprintf('/v1/ndla/legacynode/%s', $newId);
                $response = $this->client->get($path);

                $result = json_decode($response->getBody()->getContents());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("JSON decode error (" . json_last_error() . ") " . json_last_error_msg(), json_last_error());
                }

                $oldId = $result->nodeId;
                if ($oldId) {
                    Cache::put($cacheKey, $oldId, $cacheTime);
                }
            } catch (ClientException $e) {
                Log::warning(__METHOD__ . ': (' . $e->getCode() . ') ' . $e->getMessage());
                $oldId = null;
            } catch(\Throwable $t){
                Log::warning(__METHOD__ . ': (' . $t->getCode() . ') ' . $t->getMessage());
                throw $t;
            }
        }

        return $oldId;
    }
}
