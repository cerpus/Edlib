<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\OembedRequest;
use App\Oembed\OembedFormat;
use App\Oembed\RichContentResponse;
use App\Oembed\Serializer;
use Illuminate\Http\Response;

use function htmlspecialchars;
use function preg_quote;
use function sprintf;

use const ENT_HTML5;
use const ENT_QUOTES;

final readonly class OembedController
{
    public function __construct(private Serializer $serializer) {}

    public function __invoke(OembedRequest $request): Response
    {
        $format = OembedFormat::from($request->validated('format', 'json'));
        $url = $request->validated('url');

        if (!preg_match('!\A' . preg_quote($request->getUriForPath('/content/'), '!') . '([^/]+?)\z!', $url)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // TODO: improve this
        $data = $this->serializer->serialize(new RichContentResponse(
            html: sprintf(<<<EOHTML
            <iframe src="%s"></iframe>
            EOHTML, htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            width: 640,
            height: 480,
        ), $format);

        return new Response($data, headers: [
            'Content-Type' => $format->getContentType(),
        ]);
    }
}
