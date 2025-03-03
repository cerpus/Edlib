<?php

declare(strict_types=1);

namespace App\Oembed;

class RichContentResponse
{
    /**
     * @param positive-int $width
     * @param positive-int $height
     */
    public function __construct(
        public readonly string $html,
        public readonly int $width,
        public readonly int $height,
        public readonly string|null $title = null,
    ) {}

    /**
     * @return array<string, string|int>
     */
    public function getData(): array
    {
        return [
            'version' => '1.0',
            'type' => 'rich',
            'html' => $this->html,
            'width' => $this->width,
            'height' => $this->height,
            ...($this->title !== null ? ['title' => $this->title] : []),
        ];
    }
}
