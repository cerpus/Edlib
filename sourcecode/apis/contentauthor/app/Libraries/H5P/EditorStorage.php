<?php

namespace App\Libraries\H5P;

use App\H5PFile;
use App\H5PLibrary;
use App\H5PLibraryLanguage;
use App\Libraries\DataObjects\ContentStorageSettings;
use H5peditorFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Request;

/**
 * Handles all communication with the database.
 */
class EditorStorage implements \H5peditorStorage
{
    public const FILE_TEMPORARY = 'temporary';
    public const FILE_KEEP = 'keep';
    public const FILE_REMOVE = 'remove';

    private $core;

    public function __construct(\H5PCore $core)
    {
        $this->core = $core;
    }

    public function getLanguage($name, $majorVersion, $minorVersion, $language)
    {
        $library = H5PLibraryLanguage::fromLibrary([$name, $majorVersion, $minorVersion])
            ->where('language_code', $language)
            ->select('translation')
            ->first();

        return !is_null($library) ? $library->translation : false;
    }

    public function keepFile($fileId)
    {
        // TODO: No longer a tmp file.
    }

    public function getLibraries($libraries = null)
    {
        if ($libraries !== null) {
            $libraries = collect($libraries);
            return H5PLibrary::whereNotNull('semantics')
                ->where(function ($query) use ($libraries) {
                    $libraries
                        ->each(function ($library) use ($query) {
                            $query->orWhere(function ($query) use ($library) {
                                $query->fromLibrary([$library->name, $library->majorVersion, $library->minorVersion]);
                            });
                        });
                })
                ->get()
                ->sortBy(function ($library) use ($libraries) {
                    return $libraries->search(function ($item) use ($library) {
                        return $library->name === $item->name;
                    });
                })
                ->values()
                ->map(function ($h5pLibrary) {
                    /** @var H5PLibrary $h5pLibrary */
                    $library = [
                        'uberName' => $h5pLibrary->getLibraryString(false),
                        'name' => $h5pLibrary->name,
                        'majorVersion' => $h5pLibrary->major_version,
                        'minorVersion' => $h5pLibrary->minor_version,
                        'id' => $h5pLibrary->id,
                        'tutorialUrl' => $h5pLibrary->tutorial_url,
                        'title' => $h5pLibrary->title,
                        'runnable' => $h5pLibrary->runnable,
                        'restricted' => $h5pLibrary->restricted === '1' ? true : false,
                        'metadataSettings' => json_decode($h5pLibrary->metadata_settings),
                    ];
                    return (object) $library;
                })
                ->toArray();
        }

        return H5PLibrary::whereNotNull('semantics')
            ->runnable()
            ->select(['id', 'name', 'title', 'major_version', 'minor_version', 'tutorial_url AS tutorialUrl', 'restricted', 'metadata_settings'])
            ->orderBy('name')
            ->orderBy('major_version')
            ->orderBy('minor_version')
            ->get()
            ->groupBy('name')
            ->map(function ($libraryGroup) {
                return $libraryGroup
                    ->reverse()
                    ->values()
                    ->map(function ($library, $index) {
                        /** @var H5PLibrary $library */
                        $library->restricted = $library->restricted === '1' ? true : false;
                        $library->metadataSettings = json_decode($library->metadata_settings);
                        $library->majorVersion = $library->major_version;
                        $library->minorVersion = $library->minor_version;

                        // Add new library
                        $library->uberName = $library->getLibraryString(false);
                        if ($index > 0) {
                            $library->isOld = true;
                        }
                        unset($library->major_version, $library->minor_version);
                        $library = $library->toArray();
                        return (object) $library;
                    });
            })
            ->flatten()
            ->toArray();
    }

    /**
     * Implements alterLibrarySemantics
     *
     * Gives you a chance to alter all the library files.
     */
    public function alterLibraryFiles(&$files, $libraries)
    {
        return $this->core->fs->alterLibraryFiles($files);
    }

    /**
     * Saves a file or moves it temporarily. This is often necessary in order to
     * validate and store uploaded or fetched H5Ps.
     *
     * @param string $data Uri of data that should be saved as a temporary file
     * @param boolean $move_file Can be set to TRUE to move the data instead of saving it
     *
     * @return bool|object Returns false if saving failed or the path to the file
     *  if saving succeeded
     */
    public static function saveFileTemporarily($data, $move_file)
    {
        $interface = resolve(\H5PFrameworkInterface::class);
        $path = $interface->getUploadedH5pPath();

        if ($move_file) {
            if (is_uploaded_file($data)) {
                $uploadedFile = new UploadedFile($data, $path);
                $uploadedFile->storeAs(ContentStorageSettings::TEMP_DIR, $uploadedFile->getClientOriginalName(), ['disk' => 'h5pTmp']);
            }
        } else {
            // Create file from data
            file_put_contents($path, $data);
        }

        return (object) [
            'dir' => dirname($path),
            'fileName' => basename($path),
        ];
    }

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param H5peditorFile $file
     */
    public static function markFileForCleanup($file, $content_id = null)
    {
        H5PFile::create([
            'filename' => $file->getName(),
            'content_id' => !empty($content_id) ? $content_id : null,
            'user_id' => \Session::get('authId'),
            'file_hash' => $file->hash ?? null,
            'external_reference' => $file->name ?? null,
            'requestId' => Request::header('X-Request-id'),
        ]);
    }

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath)
    {
        // TODO: Implement removeTemporarilySavedFiles() method.
    }

    /**
     * Load a list of available language codes from the database.
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @return array List of possible language codes
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion)
    {
        return H5PLibraryLanguage::fromLibrary([$machineName, $majorVersion, $minorVersion])
            ->select('language_code')
            ->get()
            ->pluck('language_code')
            ->prepend('en')
            ->toArray();
    }
}
