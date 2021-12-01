<?php

namespace App\Libraries\NDLA\Importers;

use Exception;
use App\H5PContent;
use App\NdlaIdMapper;
use App\Libraries\H5P\h5p;
use App\H5PContentsMetadata;
use App\Http\Libraries\License;
use App\Libraries\NDLA\Notice\Core;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Http\Controllers\Admin\NDLAMetadataImportController;
use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\NdlaUrlHelper;

abstract class ImporterBase implements ImporterInterface
{
    use LicenseHelper;

    protected $contentType;
    protected $forceInsert = false;
    protected $coreId;

    /** @var H5PAdapterInterface */
    protected $adapter;

    /** @var self::DUPLICATE_INSERT|self::DUPLICATE_UPDATE|self::DUPLICATE_ASIS */
    protected $duplicateAction = self::DUPLICATE_SKIP;

    /** @var ImportStatus */
    protected $importStatus;

    /** @var NdlaIdMapper */
    protected $idMapper;

    const DUPLICATE_INSERT = 'insert';
    const DUPLICATE_UPDATE = 'update';
    const DUPLICATE_SKIP = 'skip';

    protected $importId;

    public function __construct(NdlaIdMapper $idMapper, ImportStatus $importStatus)
    {
        $this->idMapper = $idMapper;
        $this->importStatus = $importStatus;
        $this->adapter = resolve(H5PAdapterInterface::class);
    }

    public function setImportId($importId): ImporterInterface
    {
        $this->importId = $importId;
        return $this;
    }

    public function setDuplicateAction($action)
    {
        if (!in_array($action, [
            self::DUPLICATE_INSERT,
            self::DUPLICATE_UPDATE,
            self::DUPLICATE_SKIP,
        ])) {
            throw new \Exception(sprintf('Invalid duplicate action: %s', $action));
        }
        $this->duplicateAction = $action;
    }

    public function insertOnDuplicate()
    {
        $this->setDuplicateAction(self::DUPLICATE_INSERT);

        return $this;
    }

    public function updateOnDuplicate()
    {
        $this->setDuplicateAction(self::DUPLICATE_UPDATE);

        return $this;
    }

    public function skipOnDuplicate()
    {
        $this->setDuplicateAction(self::DUPLICATE_SKIP);

        return $this;
    }


    public function getDuplicateAction()
    {
        return $this->duplicateAction;
    }

    protected function getTranslationNodes($translations)
    {
        $nodes = [];
        foreach ($translations as $translation) {
            if (($nodeId = NdlaUrlHelper::findNodeFromUrl($translation->url)) !== false) {
                $nodes[$nodeId] = $translation;
            }
        }
        ksort($nodes, SORT_NUMERIC);
        return $nodes;
    }

    protected function importTranslations($json, $contentType, $checksum)
    {
        $translations = $this->getJsonField($json, "translations");
        if (!empty($translations)) {

            $nodes = $this->getTranslationNodes($translations);
            $mainImportedNode = $this->getImportedNode(["ndla_id" => key($nodes)]);
            if (is_null($mainImportedNode)) {
                return false;
            }
            $mainImportedCaId = $mainImportedNode->getOriginal("ca_id");

            foreach ($nodes as $nodeId => $translation) {
                if (($importedNode = $this->getImportedNode([
                        "ndla_id" => $nodeId,
                        "ndla_checksum" => $checksum
                    ])) !== null
                ) {
                    $importedNodeCaId = $importedNode->ca_id;
                    $languageLinkModel = app('App\ContentLanguageLink');

                    $languageLink = $languageLinkModel->where("main_content_id",
                        $mainImportedCaId)->where("link_content_id",
                        ($mainImportedCaId == $importedNodeCaId) ? null : $importedNodeCaId)->ofContentType($contentType)->first();

                    if (is_null($languageLink)) {
                        $languageLinkModel->content_type = $contentType;
                        $languageLinkModel->language_code = $translation->language_prefix;
                        $languageLinkModel->main_content_id = $mainImportedCaId;
                        if ($mainImportedCaId != $importedNodeCaId) {
                            $languageLinkModel->link_content_id = $importedNodeCaId;
                        }
                        if ($languageLinkModel->save() !== true) {
                            throw new \Exception("Couldn't save translation for main_content_id: $mainImportedCaId and link_content_id: $importedNodeCaId");
                        }
                    }
                }
            };
        }
        return true;
    }

    protected function getImportedNode($params)
    {
        $idMapper = clone $this->idMapper;
        foreach ($params as $column => $value) {
            $idMapper = $idMapper->where($column, $value);
        }
        return $idMapper->orderBy("created_at", "desc")->first();
    }

    /**
     * @param $json
     * @throws \Exception
     */
    protected function checkImportContentType($json)
    {
        if (empty($this->contentType)) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Setup content type before importing. Exiting.');
        }
        if ($json->content_type !== $this->contentType) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Unable to handle import of type ' . $json->content_type . '.');
        }
    }

    protected function getJsonField($json, $field, $default = null, $object = null)
    {
        if (is_null($object)) {
            $object = $json;
        }

        return property_exists($object, $field) ? $object->$field : $default;
    }

    protected function initStatus($params = [])
    {
        foreach ($params as $index => $param) {
            $this->importStatus->$index = $param;
        }
    }


    protected function isResourceInCore()
    {
        return !is_null($this->coreId);
    }

    protected function registerKeywords($json)
    {
        if ($this->isResourceInCore() === true) {
            /** @var Core $coreReporter */
            $coreReporter = app(Core::class);
            $coreReporter->setCoreId($this->coreId);
            $coreReporter->notifyKeywords($json);
            $this->importStatus->report .= PHP_EOL . "Keywords sent to Core";
        }
    }


    protected function getLicense($json)
    {
        return $this->toEdLibLicenseString($json->license->license ?? '');
    }

    protected function verify($json)
    {
        $requiredProps = ['title', 'h5p_lib', 'h5p_content_data'];
        foreach ($requiredProps as $prop) {
            if (!property_exists($json, $prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Import data is missing property ' . $prop, 400);
            }
            if (empty($json->$prop)) {
                throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Property ' . $prop . ' is empty', 400);
            }
        }
    }

    protected function generateChecksumHash($json)
    {
        try {
            $hashElements = [
                $json->nodeId,
                $json->title,
                $json->h5p_lib,
                $json->h5p_content_data,
                $json->keywords,
                $json->authors,
                $json->license
            ];

            return sha1(json_encode($hashElements));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param h5p $h5p
     * @param $h5pType
     * @return bool
     */
    protected function hasLibrarySupport($h5p, $h5pType)
    {
        $h5pCore = $h5p->getH5pCore();
        $libraryInfo = $h5pCore->libraryFromString($h5pType);
        $libraryId = false;
        if (is_array($libraryInfo)) {
            return $h5pCore->h5pF->getLibraryId($libraryInfo['machineName'], $libraryInfo['majorVersion'],
                $libraryInfo['minorVersion']);
        }

        return $libraryId;
    }

    protected function createInCore($json)
    {
        if ($this->isResourceInCore() !== true) {
            /** @var Core $coreReporter */
            $coreReporter = app(Core::class);
            $coreData = $coreReporter->notify($this->h5pData['id'], $json->nodeId, $this->h5pData['title'],
                $this->h5pData['library']['name']);
            if ($coreData !== false) {
                $this->idMapper->core_id = $coreData->id;
                $this->idMapper->launch_url = $coreData->launch;
                $this->idMapper->save();
                $this->coreId = $coreData->id;
                $this->importStatus->report .= PHP_EOL . "Registered in Core";
                return true;
            } else {
                $this->importStatus->report .= PHP_EOL . "Core registration failed.";
            }
            return false;
        }
        return true;
    }

    protected function parseCreativeCommonsLicense($license)
    {
        $parsedLicense = $this->toEdLibLicenseString($license);
        if (!$parsedLicense) {
            $parsedLicense = str_replace([' ', 'cc-'], ['-', ''], $license);
        }

        return [
            $parsedLicense,
            !is_null($parsedLicense)
        ];
    }

    /**
     * @param $json
     * @param null $h5pId
     * @throws Exception
     */
    public function addMetadata($json, $h5pId = null)
    {
        /** @var H5PContentsMetadata $h5pMetadata */
        $h5pMetadata = H5PContentsMetadata::firstOrNew(['content_id' => $h5pId ?? $this->h5pData['id']]);
        $h5pMetadata->authors = $this->addAuthors($json, $h5pMetadata)->toJson();
        $h5pMetadata->license = $this->addMetadataLicense($json);
        // $h5pMetadata->default_language = $this->addDefaultLanguage($json); // Coming soon....

        if ($h5pMetadata->save() !== true) {
            throw new Exception("Could not persist metadata.");
        };

        $this->idMapper->metadata_fetch = NDLAMetadataImportController::METADATA_DATA_SET;
    }

    private function addAuthors($json, H5PContentsMetadata $h5pMetadata)
    {
        $existingAuthors = !is_null($h5pMetadata->authors) ? collect(json_decode($h5pMetadata->authors)) : collect();
        collect($this->getJsonField($json, 'authors', []))
            ->each(function ($author) use ($existingAuthors) {
                if ($existingAuthors->search(function ($existingAuthor) use ($author) {
                        return $author->title === $existingAuthor->name;
                    }) === false) {
                    $existingAuthors->push((object)[
                        'name' => $author->title,
                        'role' => "Author",
                        'readonly' => true,
                    ]);
                }
            });
        return $existingAuthors;
    }

    private function addMetadataLicense($json)
    {
        $license = $this->getLicense($json);
        if (!$license) {
            return null;
        }
        list($ccLicenseH5PStyle, $isCC) = $this->parseCreativeCommonsLicense($license);
        if ($isCC) {
            return $this->toH5PLicenseString($ccLicenseH5PStyle);
        }

        return $license;
    }

    private function addDefaultLanguage($json)
    {
        $nodeID = $this->idMapper->ndla_id;
        $translation = collect($this->getJsonField($json, 'translations', []))
            ->first(function ($translation) use ($nodeID) {
                $explodedUrl = explode('/', $translation->url);
                return $nodeID === array_pop($explodedUrl);
            });
        return $translation->language_prefix ?? null;
    }

    protected function addLicense($json)
    {
        if ($this->adapter->adapterIs('cerpus')) {
            if (config('feature.licensing')) {
                if ($h5p = $this->getH5PFromJson($json)) {
                    $license = $this->getLicense($json);
                    if ($license) {
                        /** @var License $licenseClient */
                        $licenseClient = resolve(License::class);
                        $licenseContent = $licenseClient->getOrAddContent($h5p);
                        if ($licenseContent) {
                            $license = str_replace('-4.0', '', $license);
                            $licensingResponse = $licenseClient->setLicense($license, $h5p->id);

                            $setLicense = [];
                            if (is_string($licensingResponse)) {
                                $setLicense[] = $licensingResponse;
                            } elseif (is_object($licensingResponse)) {
                                $theLicense = $licensingResponse->licenses ?? null;
                            } elseif (is_null($licensingResponse)) {
                                $theLicense[] = $licenseClient->getLicense($h5p->id);
                            }

                            if (is_array($theLicense) && !empty($theLicense)) {
                                $setLicense = implode(',', $theLicense);
                            }

                            $this->importStatus->report .= PHP_EOL . 'License set to ' . $setLicense . '.';
                        } else {
                            $this->importStatus->report .= PHP_EOL . 'Unable to add h5p to license server.';
                        }
                    } else {
                        $this->importStatus->report .= PHP_EOL . 'No license info in H5P JSON.';
                    }
                }
            } else {
                $this->importStatus->report .= PHP_EOL . 'Licensing is disabled.';
                return false;
            }
        }

        return true;
    }

    protected function getH5PFromJson($json)
    {
        $idMapper = NdlaIdMapper::h5pByNdlaId($json->nodeId);
        if (!$idMapper) {
            $this->importStatus->report .= PHP_EOL . __METHOD__ . ': Unknown Ndla Id';
            return false;
        }

        $h5p = H5PContent::find($idMapper->ca_id);
        if (!$h5p) {
            $this->importStatus->report .= PHP_EOL . __METHOD__ . 'No H5P linked to ndla id:' . $idMapper->ndla_id . ' exist.';
            return false;
        }

        return $h5p;
    }

}
