<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

trait TestHelpers
{
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }


    protected function getPDOConnection()
    {
        return DB::connection()->getPdo();
    }
}
