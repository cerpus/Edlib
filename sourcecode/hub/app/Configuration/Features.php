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

    public function isNoindexEnabled(): bool
    {
        return $this->enabled('noindex');
    }

    public function socialUsersAreVerified(): bool
    {
        return $this->enabled('social-users-are-verified');
    }

    public function getListPollingInterval(): string|null
    {
        return self::listPollingInterval();
    }

    public static function listPollingInterval(): string|null
    {
        $raw = config('features.list-polling-interval') ?? config('features.content-list-polling-interval');

        return self::formatPollingInterval($raw);
    }

    public function getDetailsPollingInterval(): string|null
    {
        return self::detailsPollingInterval();
    }

    public static function detailsPollingInterval(): string|null
    {
        $raw = config('features.details-polling-interval') ?? config('features.content-details-polling-interval');

        return self::formatPollingInterval($raw);
    }

    public static function formatPollingInterval(mixed $value): string|null
    {
        if ($value === null || $value === false || $value === '' || $value === 0 || $value === '0' || $value === '0s') {
            return null;
        }

        $str = trim((string) $value);
        if ($str === '' || $str === '0' || $str === '0s' || in_array(strtolower($str), ['none', 'disabled', 'false', 'off', 'null'], true)) {
            return null;
        }

        if (is_numeric($str)) {
            $num = (float) $str;
            if ($num <= 0) {
                return null;
            }

            return $str . 's';
        }

        return $str;
    }
}
