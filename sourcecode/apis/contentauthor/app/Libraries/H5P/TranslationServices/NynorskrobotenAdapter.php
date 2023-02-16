<?php

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use GuzzleHttp\Client;

use const JSON_THROW_ON_ERROR;

class NynorskrobotenAdapter implements TranslationServiceInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $apiToken,
    ) {
    }

    public function getTranslations(H5PTranslationDataObject $data): H5PTranslationDataObject
    {
        $response = $this->client->post('translate', [
            'json' => $this->convertSourceToObject($data),
        ]);
        $responseData = json_decode(
            $response->getBody()->getContents(),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return new H5PTranslationDataObject($responseData['document'], $responseData['guid']);
    }

    private function convertSourceToObject(H5PTranslationDataObject $data): array
    {
        return [
            'token' => $this->apiToken,
            'guid' => $data['id'] ?? '',
            'fileType' => 'htmlp',
            'document' => $data->getFields(),
        ];
    }
}
