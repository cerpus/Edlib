<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\ResetH5PStatics;
use Tests\Traits\WithFaker;
use Tests\Traits\MockMQ;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUpTraits()
    {
        parent::setUpTraits(); // TODO: Change the autogenerated stub

        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }

        if (isset($uses[MockMQ::class])) {
            $this->setUpMockMQ();
        }

        if (isset($uses[ResetH5PStatics::class])) {
            $this->setupResetH5PStatics();
        }

    }
}
