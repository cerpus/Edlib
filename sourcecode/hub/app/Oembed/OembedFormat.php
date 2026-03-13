<?php

declare(strict_types=1);

namespace App\Oembed;

enum OembedFormat: string
{
    case Json = 'json';
    case Xml = 'xml';

    public function getContentType(): string
    {
        return match ($this) {
            self::Json => 'application/json',
            self::Xml => 'text/xml; charset=UTF-8',
        };
    }
}
