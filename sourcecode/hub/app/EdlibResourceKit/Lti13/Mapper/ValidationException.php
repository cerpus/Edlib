<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti13\Mapper;

use Exception;
use function array_map;
use function sprintf;
use function str_repeat;

class ValidationException extends Exception
{
    /**
     * @param array{path: string, message: string, subErrors: array} $error
     */
    public function __construct(public array $error)
    {
        $fn = function (array $error, int $level = 0) use (&$fn) {
            return sprintf("%s%s: %s\n%s",
                str_repeat(' ', $level * 2),
                $error['path'],
                $error['message'],
                implode("\n", array_map(
                    fn(array $error) => $fn($error, $level + 1),
                    $error['subErrors'],
                )),
            );
        };

        parent::__construct($fn($error));
    }
}
