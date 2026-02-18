<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Models\Content;
use Exception;
use Throwable;

final class ContentLockedException extends Exception
{
    /** @var string */
    protected $message = 'The content is currently locked';

    public function __construct(
        private readonly Content $content,
        Throwable $previous = null,
    ) {
        parent::__construct(previous: $previous);
    }

    public function getContent(): Content
    {
        return $this->content;
    }
}
