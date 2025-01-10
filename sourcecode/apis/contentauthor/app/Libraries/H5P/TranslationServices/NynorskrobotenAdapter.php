<?php

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use GuzzleHttp\Client;
use SensitiveParameter;

use const JSON_THROW_ON_ERROR;

final readonly class NynorskrobotenAdapter implements TranslationServiceInterface
{
    public function __construct(
        private Client $client,
        #[SensitiveParameter]
        private string $apiToken,
    ) {}

    public function translate(string $toLanguage, H5PTranslationDataObject $data): H5PTranslationDataObject
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
            'guid' => $data->getId() ?? '',
            'fileType' => 'htmlp',
            'document' => $data->getFields(),
        ];
    }

    public function getSupportedLanguages(): array|null
    {
        return [
            'nob' => ['nno'],
        ];
    }
}
