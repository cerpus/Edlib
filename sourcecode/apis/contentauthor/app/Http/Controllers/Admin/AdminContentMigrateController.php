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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use JsonException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class AdminContentMigrateController extends Controller
{
    public function __construct(
        private readonly h5p                   $h5p,
        private readonly H5PCore               $h5pCore,
        private readonly H5PFrameworkInterface $framework,
        private readonly HubClient             $hubClient,
    )
    {
    }

    public function index(Request $request): View
    {
        $pageSize = 25;
        $page = (int)$request->get('page', 1);
        $contents = collect();
        $count = 0;

        $fromLibraries = H5PLibrary::whereIn('name', ['H5P.NDLAThreeImage', 'H5P.ThreeImage'])
            ->where('major_version', 0)
            ->where('minor_version', 5)
            ->get()
            ->keyBy('name');
        $toLibrary = H5PLibrary::where('name', 'H5P.EscapeRoom')
            ->where('major_version', 0)
            ->where('minor_version', 7)
            ->first();

        if ($fromLibraries->isNotEmpty() && $toLibrary !== null) {
            if ($request->method() === 'POST' && $request->has('content')) {
                $migrated = $this->migrate($fromLibraries, $toLibrary, $request->input('content'));
            }

            $leaves = $fromLibraries
                ->flatMap(function (H5PLibrary $fromLibrary) {
                    return collect($this->getLeaves($fromLibrary->name))
                        ->map(fn (array $leaf) => $leaf + ['source_library' => $fromLibrary->name]);
                })
                ->values();
            $routePrefix = route('h5p.ltishow', '') . '/';

            // Extract H5P IDs from the leaf launch URLs
            $leafsByH5pId = collect($leaves)
                ->keyBy(function (array $leaf) use ($routePrefix) {
                    $url = $leaf['lti_launch_url'];
                    if (!str_starts_with($url, $routePrefix)) {
                        return null;
                    }
                    $h5pId = substr($url, strlen($routePrefix));
                    return is_numeric($h5pId) ? (int)$h5pId : null;
                })
                ->forget('');

            // Single query to fetch all matching H5P content
            $h5pContents = H5PContent::whereIn('library_id', $fromLibraries->pluck('id'))
                ->whereIn('id', $leafsByH5pId->keys())
                ->get()
                ->keyBy('id');

            // Build list of Hub resources with their CA content
            $items = $h5pContents->map(function (H5PContent $h5pContent) use ($leafsByH5pId) {
                $leaf = $leafsByH5pId[$h5pContent->id];

                return (object)[
                    'h5p_id' => $h5pContent->id,
                    'title' => $leaf['title'] ?: $h5pContent->title,
                    'source_library' => $leaf['source_library'],
                    'hub_content_id' => $leaf['content_id'],
                    'update_url' => $leaf['update_url'],
                ];
            })->values();

            $count = $items->count();
            $contents = $items->slice($pageSize * ($page - 1), $pageSize)->values();
        }

        return view('admin.migrate.index', [
            'fromLibraries' => $fromLibraries,
            'toLibrary' => $toLibrary,
            'migrated' => $migrated ?? [],
            'paginator' => (new LengthAwarePaginator($contents, $count, $pageSize))
                ->withPath(route('admin.migrate.library-content')),
        ]);
    }

    /**
     * @param Collection<int|string, H5PLibrary> $fromLibraries
     * @param array<array{h5p_id: string, update_url: string}> $items
     */
    private function migrate(Collection $fromLibraries, H5PLibrary $toLibrary, array $items): array
    {
        $migrated = [];
        $runId = Uuid::uuid4()->toString();
        $librariesById = $fromLibraries->keyBy('id');

        foreach ($items as $item) {
            $h5pId = $item['h5p_id'] ?? null;
            $updateUrl = $item['update_url'] ?? null;

            if (!$h5pId || !$updateUrl) {
                continue;
            }

            $sourceH5p = H5PContent::where('id', $h5pId)
                ->whereIn('library_id', $librariesById->keys())
                ->first();
            if ($sourceH5p !== null) {
                $fromLibrary = $librariesById[$sourceH5p->library_id];
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
                    $newParameters = $this->alterParameters($sourceH5p->parameters, $fromLibrary->name);
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
                        'Migrate content from ' . $fromLibrary->name . ' to ' . $toLibrary->name,
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
    private function alterParameters(string $parameters, string $libraryName): string
    {
        $content = json_decode($parameters, associative: true);
        if ($libraryName == 'H5P.ThreeImage') {
            $content['threeImage']['wasConvertedFromVirtualTour'] = true;
        }
        if (isset($content["threeImage"]["scenes"])) {
            $scenes = $content["threeImage"]["scenes"];
            $scenes = $this->migrateScenes($scenes, $libraryName);
            $content["threeImage"]["scenes"] = $scenes;
        }
        foreach ($this->l10nDefaults($libraryName) as $key => $translation) {
            $content["l10n"][$key] = $content["l10n"][$key] ?? $translation;
        }

        return json_encode($content, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * Enable zoom for scenes, and for H5P.ThreeImage (Virtual Tour) content adapt the interactions
     * to the layout Escape Room uses for converted content.
     *
     * @param array $scenes
     *
     * @return array
     */
    private function migrateScenes(array $scenes, string $libraryName): array
    {
        for ($i = 0; $i < count($scenes); $i++) {
            $scenes[$i]["enableZoom"] = true;

            if ($libraryName == 'H5P.ThreeImage') {
                for ($j = 0; $j < count($scenes[$i]["interactions"] ?? []); $j++) {
                    $scenes[$i]["interactions"][$j]["iconTypeTextBox"] = "text-icon";
                    $scenes[$i]["interactions"][$j]["showAsHotspot"] = false;
                    $scenes[$i]["interactions"][$j]["showAsOpenSceneContent"] = false;
                }
            }
        }

        if ($libraryName == 'H5P.NDLAThreeImage') {
            $scenes = $this->fixHotspotInteractionPositions($scenes);
        }

        return $scenes;
    }

    /**
     * Compensate for `transform: translate(-50%, -50%)` set on "nav buttons" shown as hotspot other than
     * GoToScene in static scenes and panorama scenes.
     *
     * @param array $scenes
     *
     * @return array
     */
    private function fixHotspotInteractionPositions(array $scenes): array
    {
        foreach ($scenes as $sceneIndex => $scene) {
            if (($scene["sceneType"] ?? null) === "360" || !isset($scene["interactions"])) {
                continue;
            }

            foreach ($scene["interactions"] as $interactionIndex => $interaction) {
                if (
                    ($interaction["showAsHotspot"] ?? null) !== true ||
                    !isset($interaction["action"]["library"]) ||
                    $interaction["action"]["library"] === "H5P.GoToScene 0.1"
                ) {
                    continue;
                }

                $positions = isset($interaction["interactionpos"])
                    ? explode(",", $interaction["interactionpos"])
                    : [];
                $sizes = isset($interaction["hotspotSettings"]["hotSpotSizeValues"])
                    ? explode(",", $interaction["hotspotSettings"]["hotSpotSizeValues"])
                    : [];

                if (count($positions) < 2 || count($sizes) < 2) {
                    continue;
                }
                // `interactionpos` is something like "1.234%,5.678%"
                $left = (float)trim($positions[0]);
                $top = (float)trim($positions[1]);

                // `hotSpotSizeValues` is something like "1.234,5.678"
                $width = (float)$sizes[0];
                $height = (float)$sizes[1];

                $scenes[$sceneIndex]["interactions"][$interactionIndex]["interactionpos"] =
                    ($left + $width / 2) . "%," . ($top + $height / 2) . "%";
            }
        }

        return $scenes;
    }

    /**
     * Default translations for Escape Room UI strings that Virtual Tour content does not have yet.
     *
     * @return array<string, string>
     */
    private function l10nDefaults(string $libraryName): array
    {
        $defaults = [
            "buttonZoomIn" => "Zoom in",
            "buttonZoomOut" => "Zoom out",
            "zoomToolbar" => "Zoom toolbar",
            "zoomAriaLabel" => "num% zoomed in",
        ];

        if ($libraryName == 'H5P.ThreeImage') {
            return [
                "title" => "Title",
                "playAudioTrack" => "Play audio track",
                "pauseAudioTrack" => "Pause audio track",
                "sceneDescription" => "Scene description",
                "resetCamera" => "Reset camera",
                "submitDialog" => "Submit dialog",
                "closeDialog" => "Close dialog",
                "expandButtonAriaLabel" => "Expand the visual label",
                "backgroundLoading" => "Loading background image ...",
                "noContent" => "No content",
                "goToScene" => "Go to scene",
                "edit" => "Edit",
                "delete" => "Delete",
                "score" => "Score",
                "assignment" => "Assignment",
                "total" => "Total",
                "scoreSummary" => "Show score summary",
                "scene" => "Scene",
                "untitled" => "Untitled",
                "userIsAtStartScene" => "You are at the start scene",
                "unlocked" => "Unlocked",
                "locked" => "Locked",
                "searchRoomForCode" => "Search the room until you find the code",
                "wrongCode" => "The code was wrong, try again.",
                "contentUnlocked" => "The content has been unlocked!",
                "code" => "Code",
                "lockedStateAction" => "Unlock",
                "hotspotDragHorizAlt" => "Drag horizontally to scale",
                "hotspotDragVertiAlt" => "Drag vertically to scale",
                "hint" => "Hint",
                "lockedContent" => "Locked content",
                "back" => "Back",
                "buttonFullscreenEnter" => "Enter fullscreen mode",
                "buttonFullscreenExit" => "Exit fullscreen mode",
                "mainToolbar" => "Main toolbar",
                "noValidSceneSet" => "No valid scenes have been set.",
            ] + $defaults;
        }

        return $defaults;
    }

    /**
     * @return array<array{lti_launch_url: string, title: string, content_id: string, update_url: string}>
     * @throws GuzzleException|JsonException|RuntimeException
     */
    private function getLeaves(string $machineName): array
    {
        $decoded = $this->hubClient->post(
            '/content-versions/leaves',
            ['tag' => 'h5p:h5p.' . strtolower($machineName)],
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
