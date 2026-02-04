<?php

namespace App\Http\Controllers\Admin;

use App\AuditLog;
use App\ContentBulkExclude;
use App\ContentVersion;
use App\Events\H5PWasSaved;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Http\Requests\AdminTranslationContentRequest;
use App\Http\Requests\AdminTranslationUpdateRequest;
use App\Libraries\H5P\AdminConfig;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class AdminH5PTranslation
{
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

        return view('admin.content-translation-update', [
            'libraryName' => $library->getLibraryString(true),
            'contentCount' => $this->getContentCount($library->id, $locale),
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

        $params->each(function ($item, $id) use (&$failed, &$unchanged, &$updated, &$messages, $request) {
            try {
                $decoded = json_decode($item, flags: JSON_THROW_ON_ERROR);

                if (empty($decoded)) {
                    // Failed on client side, error message is already be displayed
                    $failed[] = $id;
                } else {
                    /** @var H5PContent $original */
                    $original = H5PContent::findOrFail($id);
                    $version = ContentVersion::latestLeaf($original->version_id);

                    if ($original->version_id !== $version->id) {
                        $unchanged[] = $id;
                        $messages[] = 'Content ' . $id . ' is not latest version, leaving unchanged';
                    } else {
                        // JSON stored in database has escaped unicode and slashes, the JS encoded content in $item
                        // does not, so we re-encode
                        $parameters = json_encode($decoded, flags: JSON_THROW_ON_ERROR);
                        if ($parameters === $original->parameters) {
                            $messages[] = 'Content ' . $id . ' not changed';
                            $unchanged[] = $id;
                        } else {
                            $original->parameters = $parameters;
                            $original->filtered = '';
                            if ($original->save() !== true) {
                                throw new \Exception('Content ' . $id . ': Failed saving parameters');
                            }
                            // Trigger creation of new version log entry
                            event(new H5PWasSaved($original, $request, ContentVersion::PURPOSE_UPDATE, $original));
                            $messages[] = 'Content ' . $id . ' updated';
                            $updated[] = $id;
                        }
                    }
                }
            } catch (Exception $e) {
                $failed[] = $id;
                $messages[] = $e->getMessage();
            }
        });

        if (count($unchanged) > 0 || count($updated) > 0 || count($failed) > 0) {
            AuditLog::log('Content type translation update', json_encode([
                'runId' => $runId,
                'libraryId' => $libraryId,
                'libraryName' => $library->getLibraryString(true),
                'locale' => $locale,
                'unchanged' => $unchanged,
                'updated' => $updated,
                'failed' => $failed,
                'excluded' => $this->getExcludedContent($libraryId, $locale)->get()->pluck('content_id')->toArray(),
            ]));
            $messages[] = sprintf('Content updated/unchanged/failed: %d / %d / %d', count($updated), count($unchanged), count($failed));
        }
        $response = (object) [
            'params' => [],
            'left' => 0,
            'messages' => $messages,
        ];

        $contentQuery = $this->getContentQuery($libraryId, $locale)
            ->where('content_versions.content_id', '>', $params->count() > 0 ? $params->keys()->last() : 0)
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

        return [
            'library' => $library,
            'languageCode' => $locale,
            'translationDb' => $libLang,
            'translationFile' => $fileTranslation ?? null,
            'updatableCount' => $this->getContentCount($library->id, $locale),
            'excludedCount' => $this->getExcludedContent($library->id, $locale)->count(),
        ];
    }

    private function getContentQuery(int $libraryId, string $locale): Builder
    {
        return H5PContent::leftJoin('content_versions', 'content_versions.id', '=', 'h5p_contents.version_id')
            ->leftJoin('content_versions as cv', 'cv.parent_id', '=', 'content_versions.id')
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->leftJoin('content_bulk_excludes', function ($query) {
                $query->on('content_bulk_excludes.content_id', '=', 'h5p_contents.id')
                ->where('content_bulk_excludes.exclude_from', '=', ContentBulkExclude::BULKACTION_LIBRARY_TRANSLATION);
            })
            ->whereNull('content_bulk_excludes.id')
            ->where('h5p_contents.library_id', $libraryId)
            ->where('h5p_contents_metadata.default_language', $locale)
            ->where(function ($query) {
                $query
                    ->whereNull('cv.content_id')
                    ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            });
    }

    private function getContentCount(int $libraryId, string $locale): int
    {
        return $this->getContentQuery($libraryId, $locale)
            ->select(DB::raw('count(distinct(h5p_contents.id)) as total'))
            ->first()
            ->getAttribute('total');
    }

    private function getExcludedContent(int $libraryId, string $locale): Builder
    {
        return ContentBulkExclude::select(['content_bulk_excludes.content_id'])
            ->leftJoin('h5p_contents', 'h5p_contents.id', '=', 'content_bulk_excludes.content_id')
            ->join('h5p_contents_metadata', 'h5p_contents_metadata.content_id', '=', 'content_bulk_excludes.content_id')
            ->where('exclude_from', ContentBulkExclude::BULKACTION_LIBRARY_TRANSLATION)
            ->where('h5p_contents.library_id', $libraryId)
            ->where('h5p_contents_metadata.default_language', $locale);
    }
}
