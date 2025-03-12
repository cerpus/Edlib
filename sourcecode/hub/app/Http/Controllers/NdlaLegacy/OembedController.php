<?php

declare(strict_types=1);

namespace App\Http\Controllers\NdlaLegacy;

use App\Configuration\NdlaLegacyConfig;
use App\Http\Requests\NdlaLegacy\OembedRequest;
use App\Models\Content;
use App\Oembed\OembedFormat;
use App\Oembed\RichContentResponse;
use App\Oembed\Serializer;
use Illuminate\Http\Response;

/**
 * @deprecated This exists for compatibility with old integrations. New
 *     integrations with Edlib should not use this.
 */
final readonly class OembedController
{
    public function __construct(
        private NdlaLegacyConfig $config,
        private Serializer $serializer,
    ) {}

    public function content(OembedRequest $request): Response
    {
        $id = $request->getResourceId($this->config);

        $content = Content::ofTag(['prefix' => 'edlib2_usage_id', 'name' => $id])
            ->limit(1)
            ->firstOrFail();

        $format = OembedFormat::from($request->validated('format', 'json'));

        // TODO: preview
        $data = $this->serializer->serialize(new RichContentResponse(
            html: view('ndla-legacy.oembed', [
                'id' => $id,
                'src' => route('ndla-legacy.resource', [
                    $id,
                    'locale' => $request->getUrlLocale(),
                ]),
                'title' => $content->getTitle(),
            ])->render(),
            width: 800,
            height: 600,
            title: $content->getTitle(),
        ), $format);

        return new Response($data, headers: [
            'Content-Type' => $format->getContentType(),
        ]);
    }
}
