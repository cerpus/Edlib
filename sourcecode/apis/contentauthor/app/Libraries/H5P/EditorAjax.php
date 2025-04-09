<?php

namespace App\Libraries\H5P;

use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Http\Controllers\Admin\Capability;
use Illuminate\Database\Eloquent\Builder;

class EditorAjax implements \H5PEditorAjaxInterface
{
    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions()
    {
        $libraries = H5PLibrary::where('runnable', 1)
            ->with('capability')
            ->orderBy('name')
            ->orderByDesc('major_version')
            ->orderByDesc('minor_version')
            ->get()
            ->filter(function ($library) {
                if (empty($library->capability)) {
                    (new Capability())->refresh($library->id);
                    $library->refresh();
                }
                return $library->capability->enabled;
            })
            ->unique('name')
            ->all();
        return $libraries;
    }

    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @todo Get data from H5P.org
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = null)
    {
        if ($machineName) {
            return (object) H5PLibrariesHubCache::where('name', $machineName)->first()->only(['id', 'is_recommended']);
        }

        return H5PLibrariesHubCache::with('libraries.capability')
            ->get()
            ->filter(function ($cachedLibrary) {
                return $cachedLibrary->libraries->isNotEmpty() &&
                    $cachedLibrary->libraries->filter(function ($library) {
                        return $library->runnable && $library->capability->enabled;
                    })->isNotEmpty();
            });
    }

    /**
     * Gets recently used libraries for the current author
     *
     * @return array machine names. The first element in the array is the
     * most recently used.
     */
    public function getAuthorsRecentlyUsedLibraries()
    {
        return [];
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True if successful validation
     */
    public function validateEditorToken($token)
    {
        return true;
    }

    /**
     * Get translations for a language for a list of libraries
     *
     * @param array $libraries An array of libraries, in the form "<machineName> <majorVersion>.<minorVersion>
     * @param string $language_code
     * @return array
     */
    public function getTranslations($libraries, $language_code)
    {
        return H5PLibraryLanguage::with('library')
            ->where('language_code', $language_code)
            ->where(function ($query) use ($libraries) {
                collect($libraries)
                    ->each(function ($library) use ($query) {
                        $query->orWhereHas('library', function ($query) use ($library) {
                            /** @var Builder<H5PLibraryLanguage> $query */
                            $query->fromLibrary(\H5PCore::libraryFromString($library));
                        });
                    });
            })
            ->get()
            ->mapWithKeys(function ($library) {
                return [$library->library->getLibraryString(false) => $library->translation];
            })
            ->toArray();
    }
}
