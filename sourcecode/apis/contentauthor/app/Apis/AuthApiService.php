<?php

namespace App\Apis;

use App\ApiModels\User;
use App\Exceptions\NotFoundException;
use App\Util;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class AuthApiService
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => config('edlib.apis.auth.url')
        ]);
    }

    /**
     * @throws \JsonException
     */
    public function getUser(string $userId): ?User
    {
        $cacheKey = "authapiservice-user-" . $userId;

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $data = Util::handleEdlibNodeApiRequest(function () use ($userId) {
                return $this->client
                    ->getAsync("/v1/users/$userId")
                    ->wait();
            });

            $user = new User($userId, $data["firstName"] ?? '', $data["lastName"] ?? '', $data["email"]);

            Cache::put($cacheKey, $user, now()->addMinutes(60));

            return $user;
        } catch (NotFoundException $notFoundException) {
            return null;
        }
    }
}
