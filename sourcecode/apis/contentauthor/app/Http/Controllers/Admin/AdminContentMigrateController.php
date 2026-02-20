<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\AuditLog;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\Http\Controllers\Controller;
use App\Libraries\H5P\h5p;
use App\Libraries\Hub\HubClient;
use GuzzleHttp\Exception\GuzzleException;
use H5PContentValidator;
use H5PCore;
use H5PFrameworkInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use JsonException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class AdminContentMigrateController extends Controller
{
    public function __construct(
        private readonly h5p               $h5p,
        private readonly H5PCore           $h5pCore,
        private readonly H5PFrameworkInterface $framework,
        private readonly HubClient         $hubClient,
    ) {
    }

    public function index(Request $request): View
    {
        $pageSize = 25;
        $page = (int)$request->get('page', 1);
        $contents = collect();
        $count = 0;

        $fromLibrary = H5PLibrary::where('name', 'H5P.NDLAThreeImage')
            ->where('major_version', 0)
            ->where('minor_version', 5)
            ->first();
        $toLibrary = H5PLibrary::where('name', 'H5P.EscapeRoom')
            ->where('major_version', 0)
            ->where('minor_version', 7)
            ->first();

        if ($fromLibrary !== null && $toLibrary !== null) {
            if ($request->method() === 'POST' && $request->has('content')) {
                $migrated = $this->migrate($fromLibrary, $toLibrary, $request->input('content'));
            }

            $leaves = $this->getLeaves();
            $routePrefix = route('h5p.ltishow', '') . '/';

            // Extract H5P IDs from the leaf launch URLs
            $leafsByH5pId = collect($leaves)
                ->keyBy(function (array $leaf) use ($routePrefix) {
                    $url = $leaf['lti_launch_url'];
                    if (!str_starts_with($url, $routePrefix)) {
                        return null;
                    }
                    $h5pId = substr($url, strlen($routePrefix));
                    return is_numeric($h5pId) ? (int) $h5pId : null;
                })
                ->forget('');

            // Single query to fetch all matching H5P content
            $h5pContents = H5PContent::where('library_id', $fromLibrary->id)
                ->whereIn('id', $leafsByH5pId->keys())
                ->get()
                ->keyBy('id');

            // Build list of Hub resources with their CA content
            $items = $h5pContents->map(function (H5PContent $h5pContent) use ($leafsByH5pId) {
                $leaf = $leafsByH5pId[$h5pContent->id];

                return (object) [
                    'h5p_id' => $h5pContent->id,
                    'title' => $leaf['title'] ?: $h5pContent->title,
                    'hub_content_id' => $leaf['content_id'],
                    'update_url' => $leaf['update_url'],
                ];
            })->values();

            $count = $items->count();
            $contents = $items->slice($pageSize * ($page - 1), $pageSize)->values();
        }

        return view('admin.migrate.index', [
            'fromLibrary' => $fromLibrary,
            'toLibrary' => $toLibrary,
            'migrated' => $migrated ?? [],
            'paginator' => (new LengthAwarePaginator($contents, $count, $pageSize))
                ->withPath(route('admin.migrate.library-content')),
        ]);
    }

    /**
     * @param array<array{h5p_id: string, update_url: string}> $items
     */
    private function migrate(H5pLibrary $fromLibrary, H5pLibrary $toLibrary, array $items): array
    {
        $migrated = [];
        $runId = Uuid::uuid4()->toString();

        foreach ($items as $item) {
            $h5pId = $item['h5p_id'] ?? null;
            $updateUrl = $item['update_url'] ?? null;

            if (!$h5pId || !$updateUrl) {
                continue;
            }

            $sourceH5p = H5PContent::where('id', $h5pId)->where('library_id', $fromLibrary->id)->first();
            if ($sourceH5p !== null) {
                $logData = [
                    'runId' => $runId,
                    'fromLibrary' => [
                        'id' => $fromLibrary->id,
                        'name' => $fromLibrary->getLibraryString(true),
                    ],
                    'toLibrary' => [
                        'id' => $toLibrary->id,
                        'name' => $toLibrary->getLibraryString(true),
                    ],
                    'fromContentId' => $sourceH5p->id,
                    'toContentId' => null,
                    'title' => $sourceH5p->title,
                    'error' => null,
                ];
                $result = [
                    'id' => null,
                    'title' => $sourceH5p->title,
                    'message' => '',
                ];
                try {
                    $newParameters = $this->alterParameters($sourceH5p->parameters);
                    $newH5pContent = $this->save($sourceH5p, $newParameters, $fromLibrary, $toLibrary);
                    $result['id'] = $newH5pContent->id;
                    $result['message'] = 'Migrated';
                    $logData['toContentId'] = $newH5pContent->id;
                    $logData['error'] = false;
                    $this->hubClient->createContentVersion($newH5pContent, $updateUrl);
                } catch (RuntimeException|GuzzleException|JsonException $e) {
                    Log::error('Failed to migrate content: ' . $e->getMessage());
                    $result['message'] = 'Failed to migrate content: ' . $e->getMessage();
                    $logData['error'] = true;
                    $logData['errorMessage'] = $e->getMessage();
                } finally {
                    $migrated[$sourceH5p->id] = $result;
                    AuditLog::log(
                        'Migrate content from H5P.NDLAThreeImage to H5P.EscapeRoom',
                        json_encode($logData),
                    );
                }
            }
        }

        return $migrated;
    }

    /**
     * Update the semantics
     */
    private function alterParameters(string $parameters): string
    {
        $content = json_decode($parameters, associative: true);
        $content['threeImage']['wasConvertedFromVirtualTour'] = true;
        for ($i = 0; $i < count($contentJson["threeImage"]["scenes"] ?? []); $i++) {
            $contentJson["threeImage"]["scenes"][$i]["enableZoom"] = true;
            /*
             * From code at https://github.com/NDLANO/h5p-vt2er/blob/c11a34b9cdaa6842c1430a79912e78531ca21bcb/h5p-vt2er/app/H5PVT2ER.php#L299
             * May or may not be interested in adding this as well
            for ($j = 0; $j < count($contentJson["threeImage"]["scenes"][$i]["interactions"] ?? []); $j++) {
                $contentJson["threeImage"]["scenes"][$i]["interactions"][$j]["iconTypeTextBox"] = "text-icon";
                $contentJson["threeImage"]["scenes"][$i]["interactions"][$j]["showAsHotspot"] = false;
                $contentJson["threeImage"]["scenes"][$i]["interactions"][$j]["showAsOpenSceneContent"] = false;
            }
            */
        }

        return json_encode($content, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<array{lti_launch_url: string, title: string, content_id: string, update_url: string}>
     * @throws GuzzleException|JsonException|RuntimeException
     */
    private function getLeaves(): array
    {
        $decoded = $this->hubClient->post(
            '/content-versions/leaves',
            ['tag' => 'h5p:h5p.ndlathreeimage'],
        );

        return $decoded['data'] ?? [];
    }

    /**
     * @throws JsonException
     */
    private function save(H5pContent $sourceH5p, string $params, H5PLibrary $fromLibrary, H5PLibrary $toLibrary): H5pContent
    {
        $request = new Request();
        $request->attributes->set('library', $toLibrary->getLibraryString(false));
        $request->attributes->set('title', $sourceH5p->title);
        $request->attributes->set('parameters', json_encode(
            (object)[
                'params' => json_decode($params, associative: true, flags: JSON_THROW_ON_ERROR),
                'metadata' => $sourceH5p->getMetadataStructure(),
            ],
            flags: JSON_THROW_ON_ERROR,
        ));

        $request->attributes->set('isDraft', $sourceH5p->is_draft);
        $request->attributes->set('language_iso_639_3', $sourceH5p->language_iso_639_3);
        $request->attributes->set('license', $sourceH5p->license);
        $request->attributes->set('max_score', $sourceH5p->max_score);

        $oldH5p = $sourceH5p->toArray();
        $oldH5p['library'] = [
            'name' => $fromLibrary->name,
            'majorVersion' => $fromLibrary->major_version,
            'minorVersion' => $fromLibrary->minor_version,
        ];
        $oldH5p['useVersioning'] = true;
        $oldH5p['params'] = $oldH5p['parameters'];

        // Store new content and duplicate any files
        $newContent = $this->h5p->storeContent($request, $oldH5p, $sourceH5p->user_id);
        $newH5p = H5PContent::findOrFail($newContent['id']);

        // Copy license and H5P footer buttons config
        $newH5p->license = $sourceH5p->license;
        $newH5p->disable = $sourceH5p->disable;
        $newH5p->saveQuietly();

        // Update dependencies in the database from old to new content type
        $this->fixDependencies($newH5p);

        // Create new version
        event(new H5PWasSaved($newH5p, $request, ContentVersion::PURPOSE_UPDATE, $sourceH5p));

        return $newH5p;
    }

    /**
     * Delete stored dependencies for old content type, then find and store dependencies for new content type
     */
    private function fixDependencies(H5PContent $h5pContent): void
    {
        $this->framework->deleteLibraryUsage($h5pContent->id);

        // Get the dependencies based on the new main library
        $validator = new H5PContentValidator($this->framework, $this->h5pCore);
        $params = (object)[
            'library' => $h5pContent->library->getLibraryString(false),
            'params' => json_decode($h5pContent->parameters),
        ];
        $validator->validateLibrary($params, (object)[
            'options' => [
                (object)[
                    'name' => $params->library,
                ],
            ],
        ]);
        $dependencies = $validator->getDependencies();

        $this->framework->saveLibraryUsage($h5pContent->id, $dependencies);
    }
}
