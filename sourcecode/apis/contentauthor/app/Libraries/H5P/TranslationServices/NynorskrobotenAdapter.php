<?php

namespace App\Libraries\H5P\TranslationServices;

use App\Libraries\H5P\Dataobjects\H5PTranslationDataObject;
use App\Libraries\H5P\Interfaces\TranslationServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Utils as GuzzleUtils;

class NynorskrobotenAdapter implements TranslationServiceInterface
{
    public const MACHINENAME = 'nynorskroboten';
    public const TRANSLATE_ENDPOINT = '/translate';

    private $client;
    private $apiToken;

    public function __construct(Client $client, $apiToken)
    {
        $this->client = $client;
        $this->apiToken = $apiToken;
    }

    public function getTranslations(H5PTranslationDataObject $translatable): H5PTranslationDataObject
    {
        $response = $this->client->post(self::TRANSLATE_ENDPOINT, [
            'json' => $this->convertSourceToObject($translatable),
        ]);
        $responseJSONData = GuzzleUtils::jsonDecode($response->getBody()->getContents());
        $returnData = clone $translatable;
        $returnData->id = $responseJSONData->guid;
        $returnData->setFieldsFromArray((array)$responseJSONData->document);

        return $returnData;
    }

    /**
     * @param H5PTranslationDataObject $data
     * @return array
     */
    private function convertSourceToObject($data)
    {
        return [
            'token' => $this->apiToken,
            'guid' => $data->id ?? "",
            'fileType' => 'htmlp',
            'document' => $data->getDocument(),
        ];
    }
}
