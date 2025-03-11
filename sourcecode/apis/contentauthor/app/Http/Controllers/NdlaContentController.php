<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Libraries\H5P\Audio\NdlaAudioClient;
use App\Libraries\H5P\Image\NdlaImageClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function response;

final readonly class NdlaContentController
{
    public function __construct(
        private NdlaAudioClient $audioClient,
        private NdlaImageClient $imageClient,
    ) {}

    public function browseAudio(Request $request): Response
    {
        $query = [
            'query' => $request->input('query.query'),
            'fallback' => $request->input('query.fallback', 'true'),
            'page-size' => $request->input('query.pageSize'),
            'language' => $request->input('query.language'),
        ];

        $request = $this->audioClient->get('/audio-api/v1/audio', [
            'query' => $query,
        ]);
        $audios = $request->getBody()->getContents();

        return response($audios, 200, ['Content-Type' => 'application/json']);
    }

    public function getAudio($audioId, Request $request): Response
    {
        $request = $this->audioClient->get('/audio-api/v1/audio/' . $audioId, [
            'query' => [
                'language' => $request->input('language'),
            ],
        ]);
        $audio = $request->getBody()->getContents();

        return response($audio, 200, ['Content-Type' => 'application/json']);
    }

    public function browseImages(Request $request): Response
    {
        $request = $this->imageClient->request('GET', '/image-api/v3/images', [
            'query' => [
                'page' => $request->input('page', 1),
                'query' => $request->input('searchstring'),
                'language' => $request->input('language'),
                'fallback' => $request->input('fallback'),
            ],
        ]);

        $images = $request->getBody()->getContents();

        return response($images, 200, ['Content-Type' => 'application/json']);
    }

    public function getImage($imageId, Request $request): Response
    {
        $language = $request->input('language');

        $request = $this->imageClient->request('GET', '/image-api/v3/images/' . $imageId, [
            'query' => [
                'language' => $language,
            ],
        ]);
        $image = $request->getBody()->getContents();

        return response($image, 200, ['Content-Type' => 'application/json']);
    }
}
