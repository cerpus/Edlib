<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class InvalidH5pPackageException extends Exception
{
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Invalid H5P package');
    }
}
