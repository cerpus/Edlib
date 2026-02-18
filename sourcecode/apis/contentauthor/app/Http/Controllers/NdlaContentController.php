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
