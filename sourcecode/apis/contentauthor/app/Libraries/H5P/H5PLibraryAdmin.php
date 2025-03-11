<?php

namespace App\Libraries\H5P;

use App\ContentVersion;
use App\Exceptions\InvalidH5pPackageException;
use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrary;
use App\Libraries\H5P\Packages\QuestionSet;
use DB;
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
        if ($params !== null) {
            if (!$request->filled('libraryId')) {
                throw new BadRequestHttpException("Missing library to update to");
            }

            collect(json_decode($params))
                ->each(function ($param, $id) use ($request) {
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
            $upgrades_script_path = $upgrades_script_url = $dev_lib['path'] . '/upgrades.js';
        } else {
            $upgrades_script_path = $core->fs->getUpgradeScript($library->name, $library->version->major, $library->version->minor);
        }

        if (!empty($upgrades_script_path)) {
            $library->upgradesScript = $core->fs->getDisplayPath() . $upgrades_script_path;
        }

        return $library;
    }

    public function updateContentTranslation(Request $request): object
    {
        $libraryId = $request->post('libraryId');
        $locale = $request->post('locale');
        $params = collect($request->post('processed'));
        $failed = 0;
        $unchanged = 0;
        $updated = 0;
        $errors = [];

        $params->each(function ($item, $id) use (&$failed, &$unchanged, &$updated, &$errors) {
            if ($item === false) {
                // Failed on client side
                $failed++;
            } else {
                try {
                    /** @var H5PContent $original */
                    $original = H5PContent::findOrFail($id);
                    $version = ContentVersion::latestLeaf($original->version_id);

                    if ($original->version_id !== $version->id) {
                        // This should not be necessary, see to-do below
                        $unchanged++;
                        $errors[] = 'Content ' . $id . ' is not latest version, skipping';
                    } else {
                        // UTF content is treated different by JS and PHP JSON encoding, so re-encode to match database
                        $parameters = json_encode(json_decode($item, flags: JSON_THROW_ON_ERROR), flags: JSON_THROW_ON_ERROR);
                        if ($parameters === $original->parameters) {
                            $unchanged++;
                        } else {
                            // Save and inform Hub
                            $updated++;
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = $e->getMessage();
                }
            }
            return [];
        });

        if ($unchanged > 0 || $updated > 0 || $failed > 0) {
            $errors[] = sprintf('Content updated/unchanged/failed: %d / %d / %d', $updated, $unchanged, $failed);
        }
        $response = (object) [
            'params' => [],
            'left' => 0,
            'errors' => $errors,
        ];

        // Todo: Only get leaf content
        $contentQuery = DB::table('content_versions')
            ->leftJoin(DB::raw('content_versions as cv'), 'cv.parent_id', '=', 'content_versions.id')
            ->where(function ($query) {
                $query
                    ->whereNull('cv.content_id')
                    ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            })
            ->join('h5p_contents', 'h5p_contents.id', '=', 'content_versions.content_id')
            ->where('h5p_contents.library_id', $libraryId)
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents_metadata.default_language', $locale)
            ->where('content_versions.content_id', '>', $params->count() > 0 ? $params->keys()->last() : 0)
            ->orderBy('h5p_contents.id');

        $response->left = $contentQuery->select(DB::raw('count(distinct(h5p_contents.id)) as total'))->first()->total;
        if ($response->left > 0) {
            $contents = $contentQuery
                ->select(DB::raw('distinct(h5p_contents.id) as id, h5p_contents.parameters'))
                ->limit(25)
                ->get();
            $response->params = $contents->map(function ($content) {
                return [
                    'id' => $content->id,
                    'params' => $content->parameters,
                ];
            });
        }

        return $response;
    }
}
