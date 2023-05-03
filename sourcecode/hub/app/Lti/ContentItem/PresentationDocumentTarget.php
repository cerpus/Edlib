<?php

declare(strict_types=1);

namespace App\Lti\ContentItem;

enum PresentationDocumentTarget: string
{
    case Embed = 'http://purl.imsglobal.org/vocab/lti/v2/lti#embed';
    case Frame = 'http://purl.imsglobal.org/vocab/lti/v2/lti#frame';
    case Iframe = 'http://purl.imsglobal.org/vocab/lti/v2/lti#iframe';
    case None = 'http://purl.imsglobal.org/vocab/lti/v2/lti#none';
    case Overlay = 'http://purl.imsglobal.org/vocab/lti/v2/lti#overlay';
    case Popup = 'http://purl.imsglobal.org/vocab/lti/v2/lti#popup';
    case Window = 'http://purl.imsglobal.org/vocab/lti/v2/lti#window';

    public static function tryFromShortName(string $name): self|null
    {
        return self::tryFrom("http://purl.imsglobal.org/vocab/lti/v2/lti#$name");
    }

    public function toShortName(): string
    {
        return explode('#', $this->value)[1];
    }
}
