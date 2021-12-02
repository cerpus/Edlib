<?php

namespace App\Bootstrap;

use Dotenv\Dotenv;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Support\Env;

class LoadDefaultEnvVariables extends LoadEnvironmentVariables
{
    protected function createDotenv($app): Dotenv
    {
        return Dotenv::create(
            Env::getRepository(),
            $app->environmentPath(),
            ['.env.common', '.env.defaults'],
            false,
        );
    }
}
