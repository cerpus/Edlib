<?php

namespace App\Libraries\H5P;

use App\AuditLog;
use App\Exceptions\InvalidH5pPackageException;
use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\Libraries\H5P\Packages\QuestionSet;
use H5PCore;
use H5PFrameworkInterface;
use H5PStorage;
use H5PValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class H5PLibraryAdmin
{
    public const BULK_UNTOUCHED = 0;
    public const BULK_UPDATED = 1;
    public const BULK_FAILED = 2;

    public function __construct(
        private H5PCore $core,
        private H5PValidator $validator,
        private H5PFrameworkInterface $framework,
        private H5PStorage $storage,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Handles uploading of an .h5p file.
     * @return mixed The content ID
     * @throws InvalidH5pPackageException
     */
    public function handleUpload(string $path, bool $upgradeOnly, bool $disableFileCheck): mixed
    {
        // Make it possible to disable file extension check
        $this->core->disableFileCheck = $disableFileCheck;

        $newPath = $this->framework->getUploadedH5pPath();

        // Move so core can validate the file extension.
        rename($path, $newPath);

        try {
            if (!$this->validator->isValidPackage(true, $upgradeOnly)) {
                @unlink($this->framework->getUploadedH5pPath());

                throw new InvalidH5pPackageException(
                    $this->validator->h5pF->getMessages('error'),
                );
            }
        } catch (\ErrorException $e) {
            // The validator does not check for file existence before reading
            $this->logger->error('Upload failed', ['exception' => $e]);
            @unlink($this->framework->getUploadedH5pPath());
            throw new InvalidH5pPackageException(
                ['An unexpected error occurred, check that the file you uploaded is a valid .h5p file'],
            );
        }

        $this->storage->savePackage(null, null, true, $upgradeOnly);

        return $this->storage->contentId;
    }

    public function upgradeProgress(Request $request)
    {
        /** @var H5PLibrary $library */
        $library = H5PLibrary::findOrFail($request->query('id'));

        $out = new \stdClass();
        $out->skipped = json_decode($request->post('skipped', '[]'));
        $out->params = [];
        $out->token = csrf_token();

        $params = $request->post('params');
        $updated = [];
        if ($params !== null) {
            if (!$request->filled('libraryId')) {
                throw new BadRequestHttpException("Missing library to update to");
            }

            collect(json_decode($params))
                ->each(function ($param, $id) use ($request, &$updated) {
                    $params = json_decode($param);
                    if (isset($params->params)) {
                        $param = json_encode($params->params);
                    }
                    $content = H5PContent::findOrFail($id);
                    $content->library_id = $request->get('libraryId');
                    $content->parameters = $param;
                    $content->filtered = '';
                    if ($content->save() !== true) {
                        throw new \Exception("Update failed");
                    }
                    $updated[] = $id;
                    if (isset($params->metadata)) {
                        $metadata = \H5PMetadata::toDBArray((array) $params->metadata);
                        unset($metadata['title']);
                        /** @var H5PContentsMetadata $H5PContentMetadata */
                        $H5PContentMetadata = H5PContentsMetadata::firstOrNew([
                            'content_id' => $id,
                        ]);
                        $H5PContentMetadata->fill($metadata);
                        $H5PContentMetadata->save();
                    }
                });
        }

        if (count($updated) > 0) {
            $toLib = H5PLibrary::find($request->get('libraryId'));
            AuditLog::log(
                'Bulk update of content library version',
                json_encode([
                    'fromLibrary' => [
                        'id' => $library->id,
                        'name' => $library->getLibraryString(true),
                    ],
                    'toLibrary' => [
                        'id' => $toLib->id,
                        'name' => $toLib->getLibraryString(true),
                    ],
                    'contentIds' => $updated,
                ])
            );
        }

        $out->left = $library->contents()->count() - count($out->skipped);
        if ($out->left) {
            $contents = collect();
            $library
                ->contents()
                ->whereNotIn('id', $out->skipped)
                ->chunk(40, function ($contentsChunk) use ($contents) {
                    foreach ($contentsChunk as $content) {
                        $contents->push($content);
                    }
                    return false;
                });

            $out->params = $contents
                ->map(function ($content) {
                    $metadata = $content->metadata()->first();
                    if (is_null($metadata)) {
                        $metadata = H5PContentsMetadata::make([
                            'title' => $content->title,
                        ]);
                    }
                    $content->parameters = sprintf('{"params":%s,"metadata":%s}', $content->parameters, \H5PMetadata::toJSON($metadata));

                    return $content;
                })
                ->pluck('parameters', 'id')
                ->toArray();
        }

        return $out;
    }

    /**
     * @return \stdClass
     */
    public function upgradeMaxscore($libraries, $scores = null)
    {
        if (!is_array($libraries)) {
            $libraries = [$libraries];
        }

        $out = new \stdClass();
        $out->params = [];
        $out->token = csrf_token();

        /** @var Collection $libraryVersions */
        $libraryVersions = H5PLibrary::select('id', 'name')->find($libraries);
        $libraryIds = $libraryVersions->pluck('id');

        if ($scores !== null) {
            collect(json_decode($scores))
                ->each(function ($scoreObject, $id) use ($libraryIds) {
                    $content = H5PContent::findOrFail($id);
                    if (!$libraryIds->contains($content->library_id)) {
                        throw new \InvalidArgumentException("Library don't match");
                    }
                    $content->max_score = $scoreObject->score;
                    $content->bulk_calculated = $scoreObject->success ? self::BULK_UPDATED : self::BULK_FAILED;
                    if ($content->save() !== true) {
                        throw new \Exception("Setting of score failed");
                    }
                });
        }

        $contentsQuery = H5PContent::whereNull('max_score')
            ->whereIn('library_id', $libraryIds)
            ->orderBy('library_id');

        $questionSetIds = $libraryVersions->where('name', QuestionSet::$machineName)->pluck('id');
        if ($questionSetIds->count() > 0) {
            $contentsQuery->orWhere(function ($query) use ($questionSetIds) {
                $query->where('max_score', 0)
                    ->where('bulk_calculated', self::BULK_UNTOUCHED)
                    ->whereIn('library_id', $questionSetIds);
            });
        }

        $out->left = $contentsQuery->count();
        if ($out->left) {
            $contents = $contentsQuery
                ->limit(100)
                ->get();

            $out->params = $contents
                ->map(function ($content) {
                    return [
                        'id' => $content->id,
                        'library' => $content->library->getLibraryString(),
                        'libraryName' => $content->library->name,
                        'libraryId' => $content->library_id,
                        'params' => $content->parameters,
                    ];
                })
                ->toArray();
        }
        return $out;
    }

    /**
     * @since 1.1.0
     * @param string $get_library
     *
     * @throws \Exception
     */
    public function upgradeLibrary(\H5PCore $core, $get_library = '')
    {
        $library_string = $get_library;

        if (!$library_string) {
            throw new \Exception('Error, missing library!');
        }

        $library_parts = explode('/', $library_string);
        if (count($library_parts) !== 4) {
            throw new \Exception('Error, invalid library!');
        }

        $library = (object) [
            'name' => $library_parts[1],
            'version' => (object) [
                'major' => $library_parts[2],
                'minor' => $library_parts[3],
            ],
        ];
        $library->semantics = $core->loadLibrarySemantics($library->name, $library->version->major, $library->version->minor);
        if ($library->semantics === null) {
            throw new \Exception('Error, could not library semantics!');
        }

        if (isset($dev_lib)) {
            $upgrades_script_path = $dev_lib['path'] . '/upgrades.js';
        } else {
            $lib = $core->h5pF->loadLibrary($library->name, $library->version->major, $library->version->minor);
            $libraryName = H5PCore::libraryToFolderName($lib);
            $upgrades_script_path = $core->fs->getUpgradeScript($libraryName, $library->version->major, $library->version->minor);
        }

        if (!empty($upgrades_script_path)) {
            $library->upgradesScript = $core->fs->getDisplayPath() . $upgrades_script_path;
        }

        return $library;
    }
}
