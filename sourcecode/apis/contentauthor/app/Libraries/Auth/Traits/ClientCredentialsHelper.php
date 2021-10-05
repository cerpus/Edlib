<?php


namespace App\Libraries\Auth\Traits;

use Log;
use Cache;
use GuzzleHttp\Client;

trait ClientCredentialsHelper
{
    public function getClientCredentials()
    {
        $cacheName = 'CerpusAuthClientCredentials';
        if (!$clientCredentials = Cache::get($cacheName)) {
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

                $clientCredentials = json_decode($authResponse->getBody());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Unable to decode token response from auth.');
                }

                $clientCredentials->expires_at = now()->addSeconds($clientCredentials->expires_in ?? 1);
                $expiresSeconds = max(15, (int)($clientCredentials->expires_in * 0.98)); // 0.98 = fetch a new token before the old token expires

                Cache::put($cacheName, $clientCredentials, now()->addSeconds($expiresSeconds));
            } catch (\Throwable $t) {
                $message = __METHOD__ . ': (' . $t->getCode() . ') ' . $t->getMessage();

                Log::error($message);

                throw new \Exception($message);
            }
        }

        return $clientCredentials;
    }

    public function getClientCredentialsAccessToken()
    {
        $cc = $this->getClientCredentials();

        return $cc->access_token ?? null;
    }
}
