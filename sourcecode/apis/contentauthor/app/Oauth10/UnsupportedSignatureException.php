<?php

namespace App\Oauth10;

use Exception;

class UnsupportedSignatureException extends Exception implements Oauth10Exception
{
    public function __construct(string $method)
    {
        parent::__construct('Oauth: Unsupported signature method: ' . $method);
    }
}
