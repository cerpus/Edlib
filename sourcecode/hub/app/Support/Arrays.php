<?php

declare(strict_types=1);

namespace App\Support;

use function get_debug_type;

final class Arrays
{
    private function __construct()
    {
    }

    public static function allAreOfType(array $array, string $type): bool
    {
        foreach ($array as $v) {
            if (get_debug_type($v) !== $type) {
                return false;
            }
        }

        return true;
    }
}
