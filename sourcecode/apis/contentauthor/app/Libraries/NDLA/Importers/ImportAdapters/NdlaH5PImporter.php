<?php

namespace App\Libraries\NDLA\Importers\ImportAdapters;

use DB;
use Exception;
use App\H5PContent;
use App\NdlaIdMapper;
use App\Libraries\H5P\h5p;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Libraries\NDLA\Traits\H5PImportType;
use App\Libraries\NDLA\Importers\ImporterBase;
use App\Libraries\NDLA\Importers\ImportStatus;
use App\Libraries\NDLA\Importers\ImporterInterface;

class NdlaH5PImporter extends ImporterBase implements ImporterInterface
{
    use H5PImportType;

    protected $contentType = "h5p_content";

    public function __construct()
    {
        parent::__construct(app(NdlaIdMapper::class), app(ImportStatus::class));
    }

    public function import($json)
    {
        $this->verify($json);
        $this->checkImportContentType($json);
        $this->initStatus([
            'report' => 'H5P ' . ($json->title ?? 'unknown title') . ' was successfully imported. ',
            'status' => Response::HTTP_CREATED,
            "checksum" => $this->generateChecksumHash($json)
        ]);

        $this->importStatus->report .= PHP_EOL . 'Adapter: ' . $this->adapter->getAdapterName() . '.';
        if ($this->importContent($json) === true) {
            $this->importTranslations($json, "h5p", $this->generateChecksumHash($json));
            $this->createInCore($json);
            $this->registerKeywords($json);
            $this->addLicense($json);
            $this->addMetadata($json);
            $this->idMapper->save();
        }

        return $this->importStatus;
    }

    public function setImportId($importId) : ImporterInterface
    {
        return $this;
    }

    private function importContent($json)
    {
        $library = $json->h5p_lib;
        $h5pContent = $json->h5p_content_data;

        if (empty($h5pContent)) {
            throw new Exception(__METHOD__ . '(' . __LINE__ . ') Error importing nodeId: ' . $json->nodeId . '. Empty h5p_content_data',
                400);
        }

        $title = $json->title;

        $h5p = new h5p(DB::connection()->getPdo());

        if (!$this->hasLibrarySupport($h5p, $library)) {
            $this->importStatus->report = " Missing support for $library. Skipping import.";
            $this->importStatus->status = 501;
            return false;
        }

        $this->idMapper = $this->idMapper->firstOrNew([
            'ndla_id' => $json->nodeId,
            'ndla_checksum' => $this->importStatus->checksum
        ]);

        $content = null;
        if (!empty($this->idMapper->id)) {
            if ($this->getDuplicateAction() === self::DUPLICATE_SKIP) {
                $this->importStatus->id = $this->idMapper->ca_id;
                $this->importStatus->report = "H5P already imported with no detected changes";
                $this->coreId = $this->idMapper->core_id;
                $this->h5pData = $h5p->getContents($this->importStatus->id);
                return true;
            } elseif ($this->getDuplicateAction() === self::DUPLICATE_UPDATE) {
                $h5p = new h5p();
                $content = $h5p->getContents($this->idMapper->ca_id);
                $content['useVersioning'] = config('feature.versioning');
            }
        }

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

        if (!$h5p->validateStoreInput($request ,$h5pModel)) {
            throw new Exception(__METHOD__ . '(' . __LINE__ . ') ' . $h5p->getErrorMessage(), 500);
        }

        $newH5P = $h5p->storeContent($request, $content);
        if ($newH5P) {
            $this->importStatus->id = $newH5P['id'];
            $this->importStatus->report .= ' Imported.';
            $params = $newH5P['params'];
            foreach ($this->postFilters as $filter) {
                $params = (new $filter)->handle($params, $newH5P, $json);
            }
            $newH5P['parameters'] = $params;
            $newH5P['library']['name'] = $newH5P['library']['machineName']; // An error from h5peditor.class.php:162 unless this is done

            /** @var H5PContent $newContent */
            $newContent = H5PContent::findOrFail($newH5P['id']);
            $newContent->parameters = $params;
            if (!empty($this->getJsonField($json, 'created', null))) {
                $newContent->setCreatedAt($json->created);
            }
            $newContent->max_score = null;
            $newContent->save();
        } else {
            throw new Exception(__METHOD__ . '(' . __LINE__ . ') Error saving h5p.', 500);
        }

        $this->h5pData = $newH5P;

        $this->idMapper->ca_id = $newH5P['id'];
        $this->idMapper->type = 'h5p';
        $this->idMapper->save();

        return true;
    }
}
