<?php

declare(strict_types=1);

namespace App\Oembed;

abstract class OembedResponse
{
    /**
     * @var array<string, string>
     */
    public readonly array $data;

    /**
     * @param array<string, string> $data
     */
    public function __construct(
        array $data,
        string|null $title = null,
    ) {
        $data = array_filter([
            'version' => '1.0',
            'type' => $this->getType(),
            'title' => $title,
        ], fn ($v) => $v !== null) + $data;

        $this->data = $data;
    }

    abstract public function getType(): string;
}
