<?php

namespace App\Http\Controllers\Admin;

use App\ContentVersion;
use App\H5PContent;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Http\Requests\AdminTranslationContentRequest;
use App\Http\Requests\AdminTranslationUpdateRequest;
use App\Libraries\H5P\AdminConfig;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\isBool;

class AdminH5PTranslation {
    /**
     * Edit translation stored in database
     */
    public function edit(H5PLibrary $library, string $locale): View
    {
        return view('admin.library-upgrade.translation', $this->translationData($library, $locale));
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

        return view('admin.library-upgrade.translation', $data);
    }

    /**
     * Refresh the translations stored in content
     */
    public function contentRefresh(H5PLibrary $library, string $locale): View
    {
        $adminConfig = app(AdminConfig::class);
        $adminConfig->getConfig();
        $adminConfig->addContentLanguageScripts();

        $contentCount = $this->getRefreshQuery($library->id, $locale)
            ->select(DB::raw('count(distinct(h5p_contents.id)) as total'));

        $jsConfig = [
            'ajaxPath' => $adminConfig->config->ajaxPath,
            'endpoint' => route('admin.library-transation-content-update', [$library, $locale]),
            'libraryId' => $library->id,
            'library' => $library->getLibraryString(false),
            'locale' => $locale,
        ];

        return view('admin.content-language-update', [
            'library' => $library,
            'languageCode' => $locale,
            'contentCount' => $contentCount->first()->total,
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
        $failed = 0;
        $unchanged = 0;
        $updated = 0;
        $messages = [];

        $libraryId = $request->validated('libraryId');
        $locale = $request->validated('locale');
        $params = collect($request->validated('processed'));

        try {
            $params->each(function ($item, $id) use (&$failed, &$unchanged, &$updated, &$messages) {
                $decoded = json_decode($item, flags: JSON_THROW_ON_ERROR);

                if (empty($decoded)) {
                    // Failed on client side
                    $failed++;
                } else {
                    /** @var H5PContent $original */
                    $original = H5PContent::findOrFail($id);
                    $version = ContentVersion::latestLeaf($original->version_id);

                    if ($original->version_id !== $version->id) {
                        // This should not be necessary, see to-do below
                        $unchanged++;
                        $messages[] = 'Content ' . $id . ' is not latest version, leaving unchanged';
                    } else {
                        $parameters = json_encode($decoded, flags: JSON_THROW_ON_ERROR);
                        if ($parameters === $original->parameters) {
                            $unchanged++;
                        } else {
//                            $original->parameters = $parameters;
//                            $original->filtered = '';
//                            $original->timestamps = false;
//                            if ($original->saveQuietly() !== true) {
//                                throw new \Exception('Content ' . $id . ': Failed saving parameters');
//                            }
                            $updated++;
                        }
                    }
                }
            });
        } catch (Exception $e) {
            $failed++;
            $messages[] = $e->getMessage();
        }

        if ($unchanged > 0 || $updated > 0 || $failed > 0) {
            $messages[] = sprintf('Content updated/unchanged/failed: %d / %d / %d', $updated, $unchanged, $failed);
        }
        $response = (object) [
            'params' => [],
            'left' => 0,
            'messages' => $messages,
        ];

        // Todo: Only get leaf content
        $contentQuery = $this->getRefreshQuery($libraryId, $locale)
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

        return response()->json($response);
    }

    private function translationData(H5PLibrary $library, string $locale): array
    {
        $libLang = H5PLibraryLanguage::where('library_id', $library->id)
            ->where('language_code', $locale)
            ->first();

        $updatableCount = $this->getRefreshQuery($library->id, $locale)
            ->select(DB::raw('count(distinct(h5p_contents.id)) as total'));

        $totalCount = DB::table('h5p_contents')
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents.library_id', $library->id)
            ->where('h5p_contents_metadata.default_language', $locale)
            ->count();

        $filename = sprintf('libraries/%s/language/%s.json', $library->getFolderName(), $locale);
        if (Storage::exists($filename)) {
            $fileTranslation = Storage::disk()->get($filename);
            $fileModified = Carbon::createFromTimestamp(Storage::disk()->lastModified($filename));
        }

        return [
            'library' => $library,
            'languageCode' => $locale,
            'translationDb' => $libLang,
            'translationFile' => $fileTranslation ?? null,
            'fileModified' => $fileModified ?? null,
            'totalCount' => $totalCount,
            'updatableCount' => $updatableCount->first()->total,
        ];
    }

    private function getRefreshQuery(int $libraryId, string $locale): Builder
    {
        return DB::table('content_versions')
            ->leftJoin(DB::raw('content_versions as cv'), 'cv.parent_id', '=', 'content_versions.id')
            ->where(function ($query) {
                $query
                    ->whereNull('cv.content_id')
                    ->orWhereNotIn('cv.version_purpose', [ContentVersion::PURPOSE_UPGRADE, ContentVersion::PURPOSE_UPDATE]);
            })
            ->join('h5p_contents', 'h5p_contents.id', '=', 'content_versions.content_id')
            ->where('h5p_contents.library_id', $libraryId)
            ->join('h5p_contents_metadata', 'h5p_contents.id', '=', 'h5p_contents_metadata.content_id')
            ->where('h5p_contents_metadata.default_language', $locale);
    }
}
