<?php

declare(strict_types=1);

namespace App\Oembed;

class RichContentResponse extends OembedResponse
{
    /**
     * @param positive-int $width
     * @param positive-int $height
     */
    public function __construct(
        string $html,
        int $width,
        int $height,
        string|null $title = null,
    ) {
        parent::__construct([
            'html' => $html,
            'width' => (string) $width,
            'height' => (string) $height,
        ], $title);
    }

    public function getType(): string
    {
        return 'rich';
    }
}
