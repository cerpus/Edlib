<?php

namespace App\Http\Libraries;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\UserServiceException;

class UserService
{
    private $client = null;
    private $accessToken;

    /**
     * UserService constructor.
     *
     */
    public function __construct()
    {
        try {
            $authClient = new Client(['base_uri' => config('cerpus-auth.server')]);
            $authResponse = $authClient->request('POST', '/oauth/token', [
                'auth' => [
                    config('cerpus-auth.key'),
                    config('cerpus-auth.secret'),
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ],
            ]);
            $oauthJson = json_decode($authResponse->getBody());
            $this->accessToken = $oauthJson->access_token;
        } catch (\Exception $e) {
            Log::error(': Unable to get token: URL: ' . config('cerpus-auth.server') . '. Wrong key/secret?');
            return false;
        }

        $this->client = new Client([
            'base_uri' => config('cerpus-auth.server'),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ]
        ]);
    }

    /**
     * @param $id
     *
     * @return mixed|null
     * @throws \App\Exceptions\UserServiceException
     */
    public function getUser($id)
    {
        if ($this->client == null) {
            return null;
        }

        try {
            $cacheKey = "authuser-" . $id;
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $request = '/v1/users/' . $id;
            $response = $this->client->get($request);
            $result = json_decode($response->getBody());

            if ($response->getStatusCode() === 200 && $result !== null) {
                Cache::put($cacheKey, $result, now()->addMinute());
                return $result;
            }

        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            Log::error($e->getMessage() . ' (' . $e->getCode() . ')');
            throw new UserServiceException('Service error', 606, $e);
        }

        throw new UserServiceException('User not found', 607);
    }
}
