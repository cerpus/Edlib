<?php

namespace App\Http\Controllers\Admin;

use App\AuditLog;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Http\Requests\AdminTranslationContentRequest;
use App\Http\Requests\AdminTranslationUpdateRequest;
use App\Libraries\H5P\AdminConfig;
use App\Libraries\H5P\h5p;
use App\Libraries\Hub\HubClient;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class AdminH5PTranslation
{
    /** @var array<int>|null Cached leaf CA content IDs */
    private ?array $leafCaContentIds = null;

    /** @var array<int, list<string>>|null Cached map of CA content ID → Hub update URLs */
    private ?array $leafUpdateUrls = null;

    /** @var array<int>|null Cached excluded CA content IDs */
    private ?array $excludedCaContentIds = null;

    /** @var array<string>|null Cached excluded Hub content IDs */
    private ?array $excludedHubContentIds = null;


    public function __construct(
        private readonly h5p $h5p,
        private readonly HubClient $hubClient,
    ) {
    }

    /**
     * Edit translation stored in database
     */
    public function edit(H5PLibrary $library, string $locale): View
    {
        return view('admin.library-translation', $this->translationData($library, $locale));
    }

    /**
     * Update the translation in database
     */
    public function update(AdminTranslationUpdateRequest $request, H5PLibrary $library, string $locale): View
    {
        $messages = collect();
        $input = $request->validated();

        if (array_key_exists('translationFile', $input) && $request->file('translationFile')->isValid()) {
            $translation = $request->file('translationFile')->getContent();
        } else {
            $translation = $input['translation'];
        }

        if (empty($translation)) {
            $messages->add('Content was empty');
        } else {
            try {
                json_decode($translation, flags: JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $messages->add($e->getMessage());
            }

            if ($messages->isEmpty()) {
                $count = $library->languages()
                    ->where('language_code', $locale)
                    ->limit(1)
                    ->update(['translation' => $translation]);

                if ($count === 0) {
                    $messages->add('No rows was updated');
                }
            }
        }

        $data = $this->translationData($library, $locale);
        $data['messages'] = $messages;

        return view('admin.library-translation', $data);
    }

    /**
     * Refresh the translations stored in content
     */
    public function contentRefresh(H5PLibrary $library, string $locale): View
    {
        $adminConfig = app(AdminConfig::class);
        $adminConfig->getConfig();
        $adminConfig->addContentLanguageScripts();

        $jsConfig = [
            'ajaxPath' => $adminConfig->config->ajaxPath,
            'endpoint' => route('admin.library-transation-content-update', [$library, $locale]),
            'libraryId' => $library->id,
            'library' => $library->getLibraryString(false),
            'locale' => $locale,
        ];

        $this->loadLeavesFromHub($library);
        $this->loadExclusionsFromHub();

        return view('admin.content-translation-update', [
            'libraryName' => $library->getLibraryString(true),
            'contentCount' => $this->getContentCount(),
            'jsConfig' => $jsConfig,
            'scripts' => $adminConfig->getScriptAssets(),
            'styles' => $adminConfig->getStyleAssets(),
        ]);
    }

    /**
     * Update changed content in database
     */
    public function contentUpdate(AdminTranslationContentRequest $request): JsonResponse
    {
        $failed = [];
        $unchanged = [];
        $updated = [];
        $messages = [];

        $libraryId = $request->validated('libraryId');
        $locale = $request->validated('locale');
        $params = collect($request->validated('processed'));

        $runId = $request->session()->get('translation_update_run_id');
        if ($runId === null) {
            $runId = Uuid::uuid4()->toString();
            Session::put('translation_update_run_id', $runId);
        }

        $library = H5PLibrary::find($libraryId);

        // Load leaf data from Hub on the first batch, then cache in session
        // for subsequent batches so newly created versions don't appear
        if (!Session::has('translation_update_leaves')) {
            $this->loadLeavesFromHub($library);
            Session::put('translation_update_leaves', [
                'ids' => $this->leafCaContentIds,
                'urls' => $this->leafUpdateUrls,
            ]);
        } else {
            $cached = Session::get('translation_update_leaves');
            $this->leafCaContentIds = $cached['ids'];
            $this->leafUpdateUrls = $cached['urls'];
        }

        $params->each(function ($item, $id) use (&$failed, &$unchanged, &$updated, &$messages, $request) {
            try {
                $decoded = json_decode($item, flags: JSON_THROW_ON_ERROR);

                if (empty($decoded)) {
                    // Failed on client side, error message is already be displayed
                    $failed[] = $id;
                } else {
                    /** @var H5PContent $original */
                    $original = H5PContent::findOrFail($id);

                    // JSON stored in database has escaped unicode and slashes, the JS encoded content in $item
                    // does not, so we re-encode
                    $parameters = json_encode($decoded, flags: JSON_THROW_ON_ERROR);
                    if ($parameters === $original->parameters) {
                        $messages[] = 'Content ' . $id . ' not changed';
                        $unchanged[] = $id;
                    } else {
                        // Create a separate new version for each Hub resource pointing
                        // to this CA content, so they each get independent content going forward.
                        $newIds = [];
                        foreach ($this->leafUpdateUrls[$id] ?? [null] as $updateUrl) {
                            $newH5p = $this->createNewVersion($original, $parameters);
                            event(new H5PWasSaved($newH5p, $request, ContentVersion::PURPOSE_UPDATE, $original));

                            if ($updateUrl !== null) {
                                try {
                                    $this->hubClient->createContentVersion($newH5p, $updateUrl);
                                } catch (\Throwable $e) {
                                    Log::warning('Failed to create Hub version for content ' . $id . ': ' . $e->getMessage());
                                }
                            }
                            $newIds[] = $newH5p->id;
                        }

                        $messages[] = 'Content ' . $id . ' updated (new ids: ' . implode(', ', $newIds) . ')';
                        $updated[] = $id;
                    }
                }
            } catch (Exception $e) {
                $failed[] = $id;
                $messages[] = $e->getMessage();
            }
        });

        if (count($unchanged) > 0 || count($updated) > 0 || count($failed) > 0) {
            $this->loadExclusionsFromHub();

            AuditLog::log('Content type translation update', json_encode([
                'runId' => $runId,
                'libraryId' => $libraryId,
                'libraryName' => $library->getLibraryString(true),
                'locale' => $locale,
                'unchanged' => $unchanged,
                'updated' => $updated,
                'failed' => $failed,
                'excluded' => $this->excludedHubContentIds ?? [],
            ]));
            $messages[] = sprintf('Content updated/unchanged/failed: %d / %d / %d', count($updated), count($unchanged), count($failed));
        }
        $response = (object) [
            'params' => [],
            'left' => 0,
            'messages' => $messages,
        ];

        $contentQuery = $this->getContentQuery($libraryId, $locale)
            ->where('h5p_contents.id', '>', $params->count() > 0 ? $params->keys()->last() : 0)
            ->orderBy('h5p_contents.id');

        $response->left = $contentQuery->select(DB::raw('count(distinct(h5p_contents.id)) as total'))->first()->getAttribute('total');
        if ($response->left > 0) {
            $contents = $contentQuery
                ->select([DB::raw('distinct(h5p_contents.id) as id'), 'h5p_contents.parameters'])
                ->limit(25)
                ->get();
            $response->params = $contents->map(function ($content) {
                return [
                    'id' => $content->getAttribute('id'),
                    'params' => $content->getAttribute('parameters'),
                ];
            });
        } else {
            Session::forget('translation_update_run_id');
            Session::forget('translation_update_leaves');
        }

        return response()->json($response);
    }

    private function translationData(H5PLibrary $library, string $locale): array
    {
        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        $filename = sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale);
        if (Storage::exists($filename)) {
            $fileTranslation = Storage::disk()->get($filename);
        }

        $this->loadLeavesFromHub($library);
        $this->loadExclusionsFromHub();

        return [
            'library' => $library,
            'languageCode' => $locale,
            'translationDb' => $libLang,
            'translationFile' => $fileTranslation ?? null,
            'updatableCount' => $this->getContentCount(),
            'excludedCount' => count($this->excludedCaContentIds ?? []),
        ];
    }

    /**
     * Query for content that should be updated: must be a Hub leaf, match
     * the library version and locale, and not be excluded.
     */
    private function getContentQuery(int $libraryId, string $locale): \Illuminate\Database\Eloquent\Builder
    {
        $leafCaIds = $this->leafCaContentIds ?? [];
        $excludedCaIds = $this->getExcludedCaContentIds();

        $query = H5PContent::join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents.library_id', $libraryId)
            ->where('h5p_contents_metadata.default_language', $locale)
            ->whereIn('h5p_contents.id', $leafCaIds);

        if (count($excludedCaIds) > 0) {
            $query->whereNotIn('h5p_contents.id', $excludedCaIds);
        }

        return $query;
    }

    /**
     * Count the number of Hub resources that will get new versions.
     * Derived purely from Hub data (leaves minus exclusions).
     */
    private function getContentCount(): int
    {
        $excludedCaIds = $this->getExcludedCaContentIds();

        $count = 0;
        foreach ($this->leafUpdateUrls ?? [] as $caId => $urls) {
            if (!in_array($caId, $excludedCaIds, true)) {
                $count += count($urls);
            }
        }

        return $count;
    }

    /**
     * Load leaf versions from Hub. Populates CA content IDs and update URLs.
     */
    private function loadLeavesFromHub(H5PLibrary $library): void
    {
        if ($this->leafCaContentIds !== null) {
            return;
        }

        $caIds = [];
        $updateUrls = [];
        $routePrefix = route('h5p.ltishow', '') . '/';
        $tag = 'h5p:' . strtolower($library->name);

        try {
            $data = $this->hubClient->post('/content-versions/leaves', [
                'tag' => $tag,
            ]);

            foreach ($data['data'] ?? [] as $leaf) {
                $launchUrl = $leaf['lti_launch_url'] ?? null;
                if ($launchUrl && str_starts_with($launchUrl, $routePrefix)) {
                    $caId = substr($launchUrl, strlen($routePrefix));
                    if (is_numeric($caId)) {
                        $caIds[] = (int) $caId;
                        if (isset($leaf['update_url'])) {
                            $updateUrls[(int) $caId][] = $leaf['update_url'];
                        }
                    }
                }
            }
        } catch (GuzzleException|\JsonException $e) {
            Log::warning(__METHOD__ . ': Failed to load leaves from Hub: ' . $e->getMessage());
        }

        $this->leafCaContentIds = array_values(array_unique($caIds));
        $this->leafUpdateUrls = $updateUrls;
    }

    /**
     * Load exclusions from Hub API. Populates both CA content IDs and Hub content IDs.
     */
    private function loadExclusionsFromHub(): void
    {
        if ($this->excludedCaContentIds !== null) {
            return;
        }

        $caIds = [];
        $hubIds = [];
        $routePrefix = route('h5p.ltishow', '') . '/';

        try {
            $data = $this->hubClient->post('/content-exclusions/list', [
                'exclude_from' => 'library_translation_update',
            ]);

            foreach ($data['data'] ?? [] as $exclusion) {
                $hubIds[] = $exclusion['content_id'];

                $launchUrl = $exclusion['lti_launch_url'] ?? null;
                if ($launchUrl && str_starts_with($launchUrl, $routePrefix)) {
                    $caId = substr($launchUrl, strlen($routePrefix));
                    if (is_numeric($caId)) {
                        $caIds[] = (int) $caId;
                    }
                }
            }
        } catch (GuzzleException|\JsonException $e) {
            Log::warning(__METHOD__ . ': Failed to load exclusions from Hub: ' . $e->getMessage());
        }

        $this->excludedCaContentIds = $caIds;
        $this->excludedHubContentIds = $hubIds;
    }

    /**
     * Get CA content IDs that are excluded from bulk translation updates.
     *
     * @return array<int>
     */
    private function getExcludedCaContentIds(): array
    {
        $this->loadExclusionsFromHub();
        return $this->excludedCaContentIds ?? [];
    }

    /**
     * Create a new H5PContent with updated parameters, preserving the
     * original content as the previous version.
     */
    private function createNewVersion(H5PContent $original, string $parameters): H5PContent
    {
        $library = $original->library;

        $request = new Request();
        $request->attributes->set('library', $library->getLibraryString(false));
        $request->attributes->set('title', $original->title);
        $request->attributes->set('parameters', json_encode(
            (object) [
                'params' => json_decode($parameters, associative: true, flags: JSON_THROW_ON_ERROR),
                'metadata' => $original->getMetadataStructure(),
            ],
            flags: JSON_THROW_ON_ERROR,
        ));
        $request->attributes->set('isDraft', $original->is_draft);
        $request->attributes->set('language_iso_639_3', $original->language_iso_639_3);
        $request->attributes->set('license', $original->license);
        $request->attributes->set('max_score', $original->max_score);

        $oldContent = $original->toArray();
        $oldContent['library'] = [
            'name' => $library->name,
            'majorVersion' => $library->major_version,
            'minorVersion' => $library->minor_version,
        ];
        $oldContent['useVersioning'] = true;
        $oldContent['params'] = $oldContent['parameters'];

        $newContent = $this->h5p->storeContent($request, $oldContent, $original->user_id);
        $newH5p = H5PContent::findOrFail($newContent['id']);

        $newH5p->license = $original->license;
        $newH5p->disable = $original->disable;
        $newH5p->saveQuietly();

        return $newH5p;
    }
}
