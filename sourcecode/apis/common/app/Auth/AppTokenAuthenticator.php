<?php

namespace App\Auth;

use App\Models\AccessToken;
use App\Models\Application;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

class AppTokenAuthenticator
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function __invoke(Request $request): ?Application
    {
        $apiKey = $request->header("x-api-key");
        $apiClientId = $request->header("x-api-client-id");

        if ($apiKey == null || $apiClientId == null) {
            $this->logger->debug('Missing authentication information');

            return null;
        }

        $accessToken = AccessToken::whereToken( $apiKey)->whereApplicationId($apiClientId)->first();

        if (!$accessToken) {
            return null;
        }

        return $accessToken->application;
    }
}
