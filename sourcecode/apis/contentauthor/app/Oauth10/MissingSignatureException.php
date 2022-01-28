<?php

namespace App\Oauth10;

use Exception;

class MissingSignatureException extends Exception implements Oauth10Exception
{
    public function __construct()
    {
        parent::__construct('Signature was not present in parameters');
    }
}
