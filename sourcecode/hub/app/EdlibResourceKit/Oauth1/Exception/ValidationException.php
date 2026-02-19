<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Oauth1\Exception;

use App\EdlibResourceKit\Exception\ExceptionInterface;
use Exception;

class ValidationException extends Exception implements ExceptionInterface
{
}
