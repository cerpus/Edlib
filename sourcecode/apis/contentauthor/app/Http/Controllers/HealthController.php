<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        $randomValue = rand(1, 100000000);
        $cacheKey = 'healthcheck-' . $randomValue;

        Cache::put($cacheKey, $randomValue, 60);

        if (Cache::get($cacheKey, '') != $randomValue) {
            return response('cache test failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /**
         * @var PDOConnection $connection
         */
        $connection = DB::connection()->getPdo();

        $query = $connection->query("SELECT '1'");
        if (!$query || $query->fetchColumn(0) !== '1') {
            return response('db test failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('Healthy as a fish!', Response::HTTP_OK);
    }
}
