<?php

namespace Tests\Stub;

use Random\Engine;

final class RandomEngineStub implements Engine
{
    public function generate(): string
    {
        return '4';
    }
}
