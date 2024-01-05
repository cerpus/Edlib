<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\PendingCommand;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Substitute for {@link TestCase::artisan()} that always returns a pending
     * command.
     * @param array<string, string> $parameters
     */
    protected function command(string $command, array $parameters = []): PendingCommand
    {
        return new PendingCommand($this, $this->app, $command, $parameters);
    }
}
