<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Represents an error that is to be reported back to the LTI tool consumer.
 */
class LtiException extends Exception
{
    public function __construct(
        string $message,
        protected readonly string $visibleMessage = '',
    ) {
        parent::__construct($message);
    }

    public function getVisibleMessage(): string
    {
        return $this->visibleMessage;
    }
}
