<?php
namespace App\Libraries\NDLA;

use App\Libraries\NDLA\Importers\ImporterBase;
use App\Libraries\NDLA\Importers\PackageImporter;
use App\Libraries\NDLA\Importers\ImporterInterface;

class Import
{
    protected $jsonData;
    public $duplicateAction = ImporterBase::DUPLICATE_SKIP;

    public function __construct($jsonData = '{}')
    {
        $this->jsonData = json_decode($jsonData);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $strippedJson = stripslashes($jsonData);
            $this->jsonData = json_decode($strippedJson);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonErr = json_last_error();
                $jsonErrMsg = json_last_error_msg();
                throw new \Exception(__METHOD__ . ': Can not import type. Error decoding json data. ' . $jsonErr . ': ' . $jsonErrMsg,
                    400);
            }
        }
    }

    public function import()
    {
        $this->verify($this->jsonData);
        $result = null;

        /** @var ImporterInterface $importer */
        $importer = $this->getImporter();
        $importer->setDuplicateAction($this->duplicateAction);
        $result = $importer->import($this->jsonData);

        return $result;
    }

    private function getImporter()
    {
        switch ($this->jsonData->content_type) {
            case 'h5p_content':
                return resolve(ImporterInterface::class);
                break;
            case 'package':
                return app(PackageImporter::class);
                break;
            default:
                throw new \Exception(__METHOD__ . ': Can not import type ' . $this->jsonData->content_type, 501);
                break;
        }
    }

    private function verify($json)
    {
        $requiredProps = ['content_type'];
        foreach ($requiredProps as $prop) {
            if (!property_exists($json, $prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ") Payload is missing property: $prop", 400);
            }
            if (empty($json->$prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Payload property: ' . $prop . ', is empty.', 400);
            }
        }
    }
}
