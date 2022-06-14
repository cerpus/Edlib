<?php

namespace Tests\Helpers;

trait TestHelpers
{
    /**
     * @todo Refactor this away; private methods shouldn't be tested directly
     */
    protected static function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        return $method->invokeArgs($obj, $args);
    }
}
