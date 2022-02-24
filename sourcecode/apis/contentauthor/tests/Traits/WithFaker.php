<?php

namespace Tests\Traits;

use Faker\Factory;
use Faker\Generator;

trait WithFaker
{
    protected Generator $faker;

    public function setUpFaker()
    {
        $this->faker = Factory::create();
    }
}
