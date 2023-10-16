<?php

declare(strict_types=1);

namespace App\Configuration;

use LogicException;

final readonly class Features
{
    public function enabled(string $name): bool
    {
        return config('features.' . $name)
            ?? throw new LogicException("The feature '$name' is not defined");
    }

    public function isSignupEnabled(): bool
    {
        return $this->enabled('sign-up');
    }

    public function isForgotPasswordEnabled(): bool
    {
        return $this->enabled('forgot-password');
    }
}
