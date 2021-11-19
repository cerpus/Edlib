<?php

namespace App\Libraries\NDLA\Importers\ImportAdapters;

use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Packages\CoursePresentation;
use Cerpus\CoreClient\DataObjects\BehaviorSettingsDataObject;
use DB;
use Log;
use App\H5PContent;
use App\Libraries\H5P\h5p;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\NdlaArticleImportStatus;
use Cerpus\VersionClient\VersionData;
use Cerpus\VersionClient\VersionClient;
use App\Libraries\NDLA\Traits\H5PImportType;
use App\Libraries\NDLA\Importers\ImporterBase;
use App\Libraries\NDLA\Importers\ImporterInterface;


class CerpusH5PImporter extends ImporterBase implements ImporterInterface
{
    use H5PImportType;

    protected $contentType = "h5p_content";

    protected $previouslyImportedContent = null;

    protected $importId = null;

    protected $pdoConn = null;

    public function import($json)
    {
        $this->verify($json);
        $this->checkImportContentType($json);
        $this->initStatus([
            'report' => 'H5P "' . ($json->title ?? 'unknown title') . '" was successfully imported. ',
            'status' => Response::HTTP_CREATED,
            "checksum" => $this->generateChecksumHash($json)
        ]);
        if ($this->getLicense($json)) {
            $this->importStatus->report .= PHP_EOL . 'Adapter: ' . $this->adapter->getAdapterName() . '.';
            NdlaArticleImportStatus::logDebug(0, "Importing H5P.", $this->importId);
            if ($this->importContent($json) === true) {
                $this->importTranslations($json, "h5p", $this->generateChecksumHash($json));
                $this->handleVersioning($json);
                $this->createInCore($json);
                $this->registerKeywords($json);
                $this->addLicense($json);
                $this->addMetadata($json);
                $this->idMapper->save();
            }
        } else {
            $this->importStatus->report = 'Import of "' . ($json->title ?? 'unknown title') . '" failed. No license set, skipping.';
            $this->importStatus->status = Response::HTTP_NOT_IMPLEMENTED;
        }

        return $this->importStatus;
    }

    public function setImportId($importId): ImporterInterface
    {
        $this->importId = $importId;

        return $this;
    }

    protected function getPdoConnection()
    {
        NdlaArticleImportStatus::logDebug(0, "Reconnecting to DB", $this->importId);

        return DB::reconnect()->getPdo();
    }

    protected function importContent($json)
    {
        $library = $json->h5p_lib;
        $h5pContent = $json->h5p_content_data;

        if (empty($h5pContent)) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Error importing nodeId: ' . $json->nodeId . '. Empty h5p_content_data',
                400);
        }

        $title = $json->title;

        $pdoConnection = $this->getPdoConnection();

        $h5p = new h5p($pdoConnection);

        NdlaArticleImportStatus::logDebug(0, "Checking library support for $library.", $this->importId);

        if (!$this->hasLibrarySupport($h5p, $library)) {
            $this->importStatus->report = " Missing support for $library. Skipping import.";
            $this->importStatus->status = 501;
            return false;
        }

        // idMapper will point to the last imported
        NdlaArticleImportStatus::logDebug(0, "Determining imported status.", $this->importId);
        $this->idMapper = $this->idMapper->firstOrNew([
            'ndla_id' => $json->nodeId,
            'type' => 'h5p', // We may have collisions with articles on ID.
        ]);

        $request = new Request();
        $request->merge([
            'title' => $title,
            'parameters' => is_object($h5pContent) ? json_encode($h5pContent) : $h5pContent,
            'library' => $library,
            'isPublished' => true,
        ]);
        $request->importRequest = true;
        $h5p->setUserId(config('ndla.userId'));
        $h5pModel = $this->idMapper->h5pContents()->first() ?? new H5PContent();

        NdlaArticleImportStatus::logDebug(0, "Validating H5P.", $this->importId);
        if (!$h5p->validateStoreInput($request, $h5pModel)) {
            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') ' . $h5p->getErrorMessage(), 500);
        }

        $content = null;
        if ($this->idMapper->ca_id) {
            $this->previouslyImportedContent = $h5p->getContents($this->idMapper->ca_id);
            $oldContent = $this->previouslyImportedContent;
            $oldContent['useVersioning'] = config('feature.versioning');
            $content = $oldContent;
        }

        NdlaArticleImportStatus::logDebug(0, "Storing new H5P", $this->importId);
        $newH5P = $h5p->storeContent($request, $content);
        if ($newH5P) {
            NdlaArticleImportStatus::logDebug(0, "Storing new H5P success", $this->importId);
            $this->importStatus->id = $newH5P['id'];
            $this->importStatus->report .= ' Imported.';
            $params = $newH5P['params'];
            foreach ($this->postFilters as $filter) {
                $params = (new $filter)->handle($params, $newH5P, $json);
            }
            $params = $this->handleBehaviorSettings($newH5P['library']['machineName'], $params);

            $newH5P['parameters'] = $params;
            $newH5P['library']['name'] = $newH5P['library']['machineName']; // An error from h5peditor.class.php:162 unless this is done

            /** @var H5PContent $newContent */
            $newContent = H5PContent::findOrFail($newH5P['id']);
            $newContent->parameters = $params;
            if (!empty($this->getJsonField($json, 'created', null))) {
                $newContent->setCreatedAt($json->created);
            }
            $newContent->is_private = $this->adapter->getDefaultImportPrivacy();
            $newContent->max_score = null;
            $newContent->is_published = 1;
            $newContent->save();
        } else {
            NdlaArticleImportStatus::logError(0, "Storing new H5P failed.", $this->importId);

            throw new \Exception(__METHOD__ . '(' . __LINE__ . ') Error saving h5p.', 500);
        }

        $this->h5pData = $newH5P;

        $this->idMapper->ca_id = $newH5P['id'];
        $this->idMapper->ndla_checksum = $this->generateChecksumHash($json);
        $this->idMapper->type = 'h5p';
        $this->idMapper->save();

        return true;
    }

    private function handleVersioning($json)
    {
        if ($h5p = $this->getH5PFromJson($json)) {
            if (!$h5p->version_id) {
                $previousVersion = $this->getParentVersionId();
                $versionPurpose = $this->getVersionPurpose();
                $versionData = new VersionData();
                $versionData->setUserId($h5p->user_id)
                    ->setExternalReference($h5p->id)
                    ->setExternalSystem(config('app.site-name'))
                    ->setExternalUrl(route('h5p.show', $h5p->id))
                    ->setOriginSystem('ndla.no')
                    ->setOriginReference($this->generateChecksumHash($json))
                    ->setOriginId($json->nodeId)
                    ->setParentId($previousVersion)
                    ->setVersionPurpose($versionPurpose)
                    ->setCreatedAt($h5p->created_at->timestamp);

                $versionClient = resolve(VersionClient::class);
                $version = $versionClient->createVersion($versionData);

                if (!$version) {
                    $message = 'Versioning failed for h5p(' . $h5p->id . '): ' . $versionClient->getErrorCode() . ': ' . $versionClient->getMessage();
                    $this->importStatus->report .= PHP_EOL . $message;
                    Log::error($message);
                    return false;
                } else {
                    $h5p->version_id = $version->getId();
                    $h5p->save();
                    $this->importStatus->report .= PHP_EOL . 'Versioned H5P. New version id: ' . $h5p->version_id . '.';
                    return true;
                }
            } else {
                $this->importStatus->report .= PHP_EOL . 'H5P already versioned as ' . $h5p->version_id . '. Skipping.';
                return true;
            }
        } else {
            $this->importStatus->report .= PHP_EOL . __METHOD__ . ' Unable to get h5p.';
            return false;
        }

        return true;
    }

    protected function getParentVersionId()
    {
        $previousVersion = null;

        if (is_array($this->previouslyImportedContent) && array_key_exists('id', $this->previouslyImportedContent)) {
            $h5pContent = H5PContent::find($this->previouslyImportedContent['id']);
            $previousVersion = $h5pContent->version_id;
        }

        return $previousVersion;
    }

    protected function getVersionPurpose()
    {
        if ($this->getParentVersionId()) {
            return VersionData::UPDATE;
        }

        return VersionData::IMPORT;
    }

    private function handleBehaviorSettings($machineName, $params)
    {
        if( $machineName !== CoursePresentation::$machineName){
            return $params;
        }
        /** @var CoursePresentation $package */
        $package = H5PPackageProvider::make($machineName, $params);
        $behaviorSettings = BehaviorSettingsDataObject::create(['showSummary' => true]);
        $params = $package->applyBehaviorSettings($behaviorSettings);
        return $params;
    }
}
