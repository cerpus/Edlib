<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Libraries\H5P\Image\NdlaImageClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function response;

final readonly class NdlaContentController
{
    public function __construct(
        private NdlaImageClient $imageClient,
    ) {
    }

    public function browseImages(Request $request): Response
    {
        $request = $this->imageClient->request('GET', '/image-api/v3/images', [
            'query' => [
                'page' => $request->input('page', 1),
                'query' => $request->input('searchString'),
                'language' => $request->input('language'),
                'fallback' => $request->input('fallback'),
            ]
        ]);

        $images = $request->getBody()->getContents();

        return response($images, 200, ['Content-Type' => 'application/json']);
    }

    public function getImage($imageId, array $params = []): Response
    {
        $language = !empty($params['language']) ? $params['language'] : null;

        $request = $this->imageClient->request('GET', '/image-api/v3/images/' . $imageId, [
            'query' => [
                'language' => $language,
            ],
        ]);
        $image = $request->getBody()->getContents();

        return response($image, 200, ['Content-Type' => 'application/json']);
    }
}
