<?php

declare(strict_types=1);

namespace App\EdlibResourceKit\Lti\Message\DeepLinking;

enum PresentationDocumentTarget: string
{
    case Embed = 'embed';
    case Frame = 'frame';
    case Iframe = 'iframe';
    case None = 'none';
    case Overlay = 'overlay';
    case Popup = 'popup';
    case Window = 'window';
}
