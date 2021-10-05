<?php

namespace App\Libraries\NDLA\Importers;

use Log;
use App\NdlaIdMapper;
use GuzzleHttp\Client;

class PackageImporter extends ImporterBase implements ImporterInterface
{
    protected $contentType = "package";

    public function __construct()
    {
        parent::__construct(app(NdlaIdMapper::class), app(ImportStatus::class));
    }

    private function verify($json)
    {
        $requiredProps = ['content_type', 'pages', 'package_title'];
        foreach ($requiredProps as $prop) {
            if (!property_exists($json, $prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Import data is missing property ' . $prop, 400);
            }
            if (empty($json->$prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Property ' . $prop . ' is empty', 400);
            }
        }
    }

    protected function checkImportContentType($json)
    {
        if (empty($this->contentType)) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Setup content type before importing. Exiting.');
        }
        if ($json->content_type !== $this->contentType) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Unable to handle import of type ' . $json->content_type . '.');
        }
    }

    public function import($json)
    {
      $this->verify($json);
      $this->checkImportContentType($json);

        $this->initStatus([
            'report' => 'Learning Path data was sucessfully imported',
            'status' => 201,
            "checksum" => $this->generateChecksumHash($json)
        ]);
        $finalJson = new \stdClass();
        $finalJson->title = $json->package_title;
        $finalJson->pages = [];
        foreach ($json->pages as $page) {
            if ($page->pos == 1) {
                $finalJson->description = strip_tags(html_entity_decode(htmlspecialchars_decode($page->content)));
                continue;
            }
            $importObject = $this->makeImportObject($page);
            if (is_null($importObject)) {
                continue;
            }
            $finalJson->pages[] = $importObject;
        }
        if (!empty($finalJson->pages)) {
            $result = $this->exportToEdStep($finalJson);
            if ($result === false) {
                $this->importStatus->report = "Could not import learning path. ";
                $this->importStatus->status = 500;
            }
        } else {
            $this->importStatus->report = "Could not import learning path. None of the pages is found in content author.";
            $this->importStatus->status = "500";
        }

        return $this->importStatus;
    }

    protected function exportToEdStep($json)
    {
        $query = [
            'form_params' => [
                'user' => 'user',
                'pass' => 'pass',
                'json' => json_encode($json)
            ],
        ];
        $edStepUrl = config('ndla.edStepUrl');
        if ($edStepUrl === false) {
            return false;
        }
        $edStepServer = config('ndla.edStepUrl') . '/api/v1/learning-path';
        try {
            $client = new Client();
            $result = $client->request('POST', $edStepServer, $query);
        } catch (\Exception $e) {
            Log::error(__METHOD__ . ': ' . $e->getCode() . ' ' . $e->getMessage());
        }

        return $result->getBody();
    }

    /**
     * @param $page
     * @return \stdClass
     */
    protected function makeImportObject($page)
    {
        $launchUrl = $this->getLaunchUrl($page->nodeId);

        if (is_null($launchUrl)) {
            Log::info('Unable to find nodeId: ' . $page->nodeId . ' in system. Skipping.');
            return null;
        }

        $finalPage = new \stdClass();
        $finalPage->title = $page->title;
        $finalPage->pos = $page->pos - 1;
        $finalPage->launch_url = $launchUrl;
        $finalPage->resource_type = $page->content_type;

        return $finalPage;
    }

    protected function getLaunchUrl($nodeId)
    {
        $nodeMap = NdlaIdMapper::where('ndla_id', $nodeId)->first();
        if (is_null($nodeMap)) {
            return null;
        }

        return $nodeMap->launch_url;
    }

    private function generateChecksumHash($json)
    {
        try {
            $hashElements = [
                $json->package_id,
                $json->package_version_id,
                $json->package_modified_date->date,
            ];

            return sha1(json_encode($hashElements));
        } catch (\Exception $e) {
            return null;
        }
    }
}