<?php

declare(strict_types=1);

namespace App\Lti\Exception;

use Exception;

class Oauth1ValidationException extends Exception implements LtiExceptionInterface
{
}
