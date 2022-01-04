<?php

namespace App\Http\Controllers;

use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function index()
    {
        /**
         * @var PDOConnection $connection
         */
        $connection = DB::connection()->getPdo();

        $query = $connection->query("SELECT '1'");
        if (!$query || $query->fetchColumn(0) !== '1') {
            return response('db test failed', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response('Healthy!', Response::HTTP_OK);
    }
}
