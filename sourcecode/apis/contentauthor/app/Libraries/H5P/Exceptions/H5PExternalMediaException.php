<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Exceptions;

use RuntimeException;
use Throwable;

class H5PExternalMediaException extends RuntimeException
{
    public function __construct(
        private readonly string $path,
        private readonly ?string $mime,
        private readonly ?int $contentId,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function getContentId(): ?int
    {
        return $this->contentId;
    }
}
