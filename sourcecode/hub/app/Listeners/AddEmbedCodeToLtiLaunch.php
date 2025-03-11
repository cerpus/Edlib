<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\LaunchContent;

use function htmlspecialchars;
use function preg_replace;

use const ENT_QUOTES;

final readonly class AddEmbedCodeToLtiLaunch
{
    /**
     * A minimal resize script. Explained, this adds a global listener for
     * messages. When a message is received, it:
     *
     * 1. checks that the iframe exists
     * 2. checks that the iframe matches the source of the message
     * 3. checks that the payload contains "action" === "resize" and
     *    "scrollHeight" properties
     * 4. sets the height of the iframe to the requested height + border
     */
    private const RESIZE_SCRIPT = <<<EOHTML
    <script>((f, h) => addEventListener('message', e => f &&
        f.contentWindow === e.source &&
        e.data && e.data.action && e.data.action === 'resize' && e.data[h] &&
        (f.height = String(e.data[h] + f.getBoundingClientRect().height - f[h]))
    ))(document.getElementById('edlib-%s'), 'scrollHeight')</script>
    EOHTML;

    public function handleLaunch(LaunchContent $event): void
    {
        $event->setLaunch(
            $event->getLaunch()
                ->withClaim('ext_edlib3_embed_code', $this->getEmbedCode($event))
                ->withClaim('ext_edlib3_embed_resize_code', $this->getResizeCode($event)),
        );
    }

    private function getEmbedCode(LaunchContent $event): string
    {
        $version = $event->getContentVersion();

        return sprintf(
            <<<EOHTML
        <iframe src="%s" title="%s" width=":w" height=":h" frameborder="0" id="edlib-%s"></iframe>
        EOHTML,
            htmlspecialchars(
                route('content.embed', [$version->content]),
                ENT_QUOTES | ENT_HTML5,
                'UTF-8',
            ),
            htmlspecialchars($version->getTitle(), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $version->id,
        );
    }

    private function getResizeCode(LaunchContent $event): string
    {
        return '' . preg_replace('/\s+/', ' ', sprintf(
            self::RESIZE_SCRIPT,
            $event->getContentVersion()->id,
        ));
    }
}
