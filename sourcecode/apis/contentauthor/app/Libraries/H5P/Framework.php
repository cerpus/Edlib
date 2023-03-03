<?php

namespace App\Libraries\H5P;

use App\H5PContent;
use App\H5PContentsMetadata;
use App\H5PLibrariesCachedAssets;
use App\H5PLibrariesHubCache;
use App\H5PLibrary;
use App\H5PLibraryLibrary;
use App\H5POption;
use App\Libraries\DataObjects\ContentStorageSettings;
use App\Libraries\H5P\Helper\H5POptionsCache;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\Result;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use H5PCore;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use TypeError;

class Framework implements \H5PFrameworkInterface, Result
{
    /** @var array<string> */
    private array $errorMessages = [];

    /** @var array<string> */
    private array $infoMessages = [];

    private $adminUrl;

    public function __construct(
        private ClientInterface $httpClient,
        private PDO $db,
        private Filesystem $disk
    ) {
    }

    // Implements result Interface
    public function handleResult($userId, $contentId, $score, $maxScore, $opened, $finished, $time, $context)
    {
        if ($this->hasResult($userId, $contentId, $context)) {
            return $this->updateResult($userId, $contentId, $score, $maxScore, $opened, $finished, $time, $context);
        }
        return $this->insertResult($userId, $contentId, $score, $maxScore, $opened, $finished, $time, $context);
    }

    private function updateResult($userId, $contentId, $score, $maxScore, $opened, $finished, $time, $context)
    {
        $sql = "update h5p_results set score=:score, max_score=:maxScore, opened=:opened, finished=:finished, time=:time where user_id=:userId and content_id=:contentId";
        $params = [
            ':userId' => $userId,
            ':contentId' => $contentId,
            ':score' => $score,
            ':maxScore' => $maxScore,
            ':opened' => $opened,
            ':finished' => $finished,
            ':time' => $time,
        ];
        $this->getContextSql($sql, $params, $context);

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        return $result;
    }

    private function insertResult($userId, $contentId, $score, $maxScore, $opened, $finished, $time, $context)
    {
        $sql = "insert into h5p_results (user_id, content_id, score, max_score, opened, finished, time, context) values (:userId, :contentId, :score, :maxScore, :opened, :finished, :time, :context)";
        $params = [
            ':userId' => $userId,
            ':contentId' => $contentId,
            ':score' => $score,
            ':maxScore' => $maxScore,
            ':opened' => $opened,
            ':finished' => $finished,
            ':time' => $time,
            ':context' => $context
        ];

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($params);
        return $result;
    }

    private function getContextSql(&$sql, &$params, $context)
    {
        if (!is_null($context)) {
            $sql .= " and context = :context";
            $params[':context'] = $context;
        } else {
            $sql .= " and context IS NULL";
        }
    }

    private function hasResult($userId, $contentId, $context)
    {
        $sql = "select id from h5p_results where user_id=:userId and content_id=:contentId ";
        $params = [
            ':userId' => $userId,
            ':contentId' => $contentId
        ];
        $this->getContextSql($sql, $params, $context);

        $result = $this->runQuery($sql, $params);
        return !empty($result);
    }

    /**
     * Returns info for the current platform
     *
     * @return array
     *   An associative array containing:
     *   - name: The name of the plattform, for instance "Wordpress"
     *   - version: The version of the pattform, for instance "4.0"
     *   - h5pVersion: The version of the H5P plugin/module
     */
    public function getPlatformInfo()
    {
        return [
            "name" => "H5PComposer",
            "version" => "0.1",
            "h5pVersion" => "1.5"
        ];
    }

    public function fetchExternalData(
        $url,
        $data = null,
        $blocking = true,
        $stream = null,
        $fullData = false,
        $headers = array(),
        $files = array(),
        $method = 'POST'
    ): string|array|null
    {
        $options = [RequestOptions::FORM_PARAMS => $data];
        if ($stream !== null) {
            $options[RequestOptions::SINK] = $stream;
        }
        $options[RequestOptions::HEADERS] = $headers;

        return $this->httpClient->requestAsync($method, $url, $options)
            ->then(static function (ResponseInterface $response) use ($blocking, $fullData) {
                if (!$blocking) {
                    return null;
                }
                if ($fullData) {
                    return [
                        'status' => $response->getStatusCode(),
                        'headers' => $response->getHeaders(),
                        'data' => $response->getBody()->getContents(),
                    ];
                }

                return $response->getBody()->getContents();
            })
            ->otherwise(fn ($e) => $e instanceof GuzzleException ? null : throw $e)
            ->wait();
    }

    /**
     * Set the tutorial URL for a library. All versions of the library is set
     *
     * @param string $machineName
     * @param string $tutorialUrl
     */
    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
        $sql = "update h5p_libraries set tutorial_url = ? where name= ?";
        $params = [$tutorialUrl, $machineName];
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute($params);
        if ($res === false) {
            throw new RuntimeException(__METHOD__ . ": Could not set tutorial url for " . $machineName);
        }
    }

    public function setErrorMessage($message, $code = null): void
    {
        // It isn't clear how $code should be used, so we just ignore it.
        $this->errorMessages[] = $message;
    }

    public function setInfoMessage($message): void
    {
        $this->infoMessages[] = $message;
    }

    public function getMessages($type): array
    {
        return match ($type) {
            'info' => $this->infoMessages,
            'error' => $this->errorMessages,
            default => throw new InvalidArgumentException('Unknown message type'),
        };
    }

    /**
     * Translation function
     *
     * @param string $message
     *  The english string to be translated.
     * @param type $replacements
     *   An associative array of replacements to make after translation. Incidences
     *   of any key in this array are replaced with the corresponding value. Based
     *   on the first character of the key, the value is escaped and/or themed:
     *    - !variable: inserted as is
     *    - @variable: escape plain text to HTML
     *    - %variable: escape text and theme as a placeholder for user-submitted
     *      content
     * @return string
     *   Translated string
     * TODO: Implement this for real....
     */
    public function t($message, $replacements = [])
    {
        foreach ($replacements as $key => $replacement) {
            $firstCharacter = $key[0];
            if ($firstCharacter == "!") {
                $message = str_replace($key, $replacement, $message);
            } elseif ($firstCharacter == "@" || $firstCharacter == "%") {
                $message = str_replace($key, htmlentities($replacement), $message);
            }
        }
        return $message;
    }

    public function getH5pPath(string $path)
    {
        return $this->disk->path($path);
    }

    /**
     * Get the Path to the last uploaded h5p
     *
     * @return string
     *   Path to the folder where the last uploaded h5p for this session is located.
     * TODO: Implement this for real....
     */
    public function getUploadedH5pFolderPath()
    {
        static $dir;

        if (is_null($dir)) {
            $dir = $this->disk->path(sprintf(ContentStorageSettings::TEMP_PATH, uniqid('h5p-')));
        }

        return $dir;
    }

    /**
     * Get the path to the last uploaded h5p file
     *
     * @return string  Path to the last uploaded h5p
     */
    public function getUploadedH5pPath()
    {
        static $path;
        if (is_null($path)) {
            $core = resolve(H5PCore::class);
            $path = $core->fs->getTmpPath() . '.h5p';
        }

        return $path;
    }

    /**
     * Get a list of the current installed libraries
     *
     * @return array
     *   Associative array containg one entry per machine name.
     *   For each machineName there is a list of libraries(with different versions)
     */
    public function loadLibraries()
    {
        return H5PLibrary::select(['id', 'name', 'title', 'major_version', 'minor_version', 'patch_version', 'runnable', 'restricted'])
            ->orderBy('major_version')
            ->orderBy('minor_version')
            ->orderBy('patch_version')
            ->getQuery()
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->name => $item];
            })
            ->sortBy(function ($item) {
                return $item->first()->title;
            })
            ->toArray();
    }

    /**
     * Saving the unsupported library list
     *
     * @param array
     *   A list of unsupported libraries. Each list entry contains:
     *   - name: MachineName for the library
     *   - downloadUrl: URL to a location a new version of the library may be downloaded from
     *   - currentVersion: The unsupported version of the library installed on the system.
     *     This is an associative array containing:
     *     - major: The major version of the library
     *     - minor: The minor version of the library
     *     - patch: The patch version of the library
     * TODO: Check if Drupal impl has something here.
     */
    public function setUnsupportedLibraries($libraries)
    {
    }

    /**
     * Returns unsupported libraries
     *
     * @return array
     *   A list of unsupported libraries. Each entry contains an associative array with:
     *   - name: MachineName for the library
     *   - downloadUrl: URL to a location a new version of the library may be downloaded from
     *   - currentVersion: The unsupported version of the library installed on the system.
     *     This is an associative array containing:
     *     - major: The major version of the library
     *     - minor: The minor version of the library
     *     - patch: The patch version of the library
     * TODO: Check if Drupal impl has something here.
     */
    public function getUnsupportedLibraries()
    {
    }


    /**
     * Returns the URL to the library admin page
     *
     * @return string
     *   URL to admin page
     * TODO: Check if Drupal impl has something here.
     */
    public function getAdminUrl()
    {
    }

    /**
     * Set the URL to the library admin page
     */
    public function setAdminUrl($url)
    {
        $this->adminUrl = $url;
    }

    /**
     * Get id to an existing library
     *
     * @param string $machineName
     *   The librarys machine name
     * @param int $majorVersion
     *   The librarys major version
     * @param int $minorVersion
     *   The librarys minor version
     * @return int
     *   The id of the specified library or FALSE
     */
    public function getLibraryId($machineName, $majorVersion = null, $minorVersion = null)
    {
        $library = H5PLibrary::select('id')
            ->where('name', $machineName)
            ->where('major_version', $majorVersion)
            ->where('minor_version', $minorVersion)
            ->first();

        if (!$library) {
            return false;
        }

        return (int)$library->id;
        /*
         * // The following code sometimes crashes(!!!?) when reached through an import. The Eloquent version seems to be stable.
         * // Laravel log: "Error SQLSTATE[HY000]: General error: 2006 MySQL server has gone away" (Very rude I feel...)
         * // MySQL error log: 2019-06-25T08:18:18.872848Z 332 [Note] Aborted connection 332 to db: 'content-author' user: 'root' host: 'localhost' (Got an error reading communication packets)

        $sql = "select id from h5p_libraries where name=? and major_version=? and minor_version=?";
        $statment = $this->db->prepare($sql);
        $statment->execute([$machineName, $majorVersion, $minorVersion]);
        $id = $statment->fetchColumn();
        if ($id === false) {
            return false;
        }
        return (int)$id;
        */
    }

    /**
     * Get file extension whitelist
     *
     * The default extension list is part of h5p, but admins should be allowed to modify it
     *
     * @param boolean $isLibrary
     *   TRUE if this is the whitelist for a library. FALSE if it is the whitelist
     *   for the content folder we are getting
     * @param string $defaultContentWhitelist
     *   A string of file extensions separated by whitespace
     * @param string $defaultLibraryWhitelist
     *   A string of file extensions separated by whitespace
     */
    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        // TODO: Get this value from a settings page.
        $whitelist = $defaultContentWhitelist;
        if ($isLibrary) {
            $whitelist .= ' ' . $defaultLibraryWhitelist;
        }
        $whitelist .= ' js';
        return $whitelist;
    }

    /**
     * Is the library a patched version of an existing library?
     *
     * @param object $library
     *   An associateve array containing:
     *   - machineName: The library machineName
     *   - majorVersion: The librarys majorVersion
     *   - minorVersion: The librarys minorVersion
     *   - patchVersion: The librarys patchVersion
     * @return boolean
     *   TRUE if the library is a patched version of an existing library
     *   FALSE otherwise
     * TODO: Implement this for real....
     */
    public function isPatchedLibrary($library)
    {
        return H5PLibrary::fromLibrary([
            $library['machineName'],
            $library['majorVersion'],
            $library['minorVersion']
        ])
            ->where('patch_version', "<", $library['patchVersion'])
            ->get()
            ->isNotEmpty();
    }

    /**
     * Is H5P in development mode?
     *
     * @return boolean
     *  TRUE if H5P development mode is active
     *  FALSE otherwise
     * TODO: Implement this for real....
     */
    public function isInDevMode()
    {
        return false;
    }

    /**
     * Is the current user allowed to update libraries?
     *
     * @return boolean
     *  TRUE if the user is allowed to update libraries
     *  FALSE if the user is not allowed to update libraries
     *  This is not accessible if logged out anyways
     */
    public function mayUpdateLibraries()
    {
        return \Session::get("isAdmin", false) || Request::is('admin/*') || Request::is("api/v1/h5p/import");
    }

    /**
     * Store data about a library
     *
     * Also fills in the libraryId in the libraryData object if the object is new
     */
    public function saveLibraryData(&$libraryData, $new = true)
    {
        /** @var array $libraryData */
        $preloadedJs = $this->pathsToCsv($libraryData, 'preloadedJs', 'path');
        $preloadedCss = $this->pathsToCsv($libraryData, 'preloadedCss', 'path');
        $dropLibraryCss = $this->pathsToCsv($libraryData, 'dropLibraryCss', 'machineName');

        $embedTypes = '';
        if (isset($libraryData['embedTypes'])) {
            $embedTypes = implode(', ', $libraryData['embedTypes']);
        }
        if (!isset($libraryData['semantics'])) {
            $libraryData['semantics'] = '';
        }
        if (!isset($libraryData['fullscreen'])) {
            $libraryData['fullscreen'] = 0;
        }

        $libraryData['metadataSettings'] = isset($libraryData['metadataSettings']) ? \H5PMetadata::boolifyAndEncodeSettings($libraryData['metadataSettings']) : null;
        $libraryData['addTo'] = isset($libraryData['addTo']) ? json_encode($libraryData['addTo']) : null;

        /** @var H5PLibrary $h5pLibrary */
        $h5pLibrary = H5PLibrary::updateOrCreate([
            'id' => !$new ? $libraryData['libraryId'] : null
        ], [
            'name' => $libraryData['machineName'],
            'title' => $libraryData['title'],
            'major_version' => $libraryData['majorVersion'],
            'minor_version' => $libraryData['minorVersion'],
            'patch_version' => $libraryData['patchVersion'],
            'runnable' => $libraryData['runnable'],
            'fullscreen' => $libraryData['fullscreen'],
            'embed_types' => $embedTypes,
            'preloaded_js' => $preloadedJs,
            'preloaded_css' => $preloadedCss,
            'drop_library_css' => $dropLibraryCss,
            'semantics' => $libraryData['semantics'],
            'metadata_settings' => $libraryData['metadataSettings'],
            'add_to' => $libraryData['addTo'],
            'has_icon' => $libraryData['hasIcon'] ?? 0,
            'tutorial_url' => ''
        ]);
        $libraryData['libraryId'] = $h5pLibrary->id;

        $h5pLibrary->libraries()->delete();
        $h5pLibrary->languages()->delete();
        if (isset($libraryData['language'])) {
            foreach ($libraryData['language'] as $languageCode => $translation) {
                $h5pLibrary->languages()->create([
                    'library_id' => $libraryData['libraryId'],
                    'language_code' => $languageCode,
                    'translation' => $translation
                ]);
            }
        }
    }

    /**
     * Insert new content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - user_id: The users ID
     *   - title: Title
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versioning
     */
    public function insertContent($content, $contentMainId = null)
    {
        /** @var H5PAdapterInterface $adapter */
        $adapter = app(H5PAdapterInterface::class);
        $metadataRaw = (array)$content['metadata'] ?? [];
        $metadata = \H5PMetadata::toDBArray($metadataRaw, true);

        $H5PContent = H5PContent::make();
        $H5PContent->title = !empty($metadata['title']) ? $metadata['title'] : $content['title'];
        $H5PContent->parameters = $content['params'];
        $H5PContent->filtered = '';
        $H5PContent->library_id = $content['library']['libraryId'];
        $H5PContent->embed_type = $content['embed_type'];
        $H5PContent->disable = $content['disable'];
        $H5PContent->max_score = !is_null($content['max_score']) ? (int)$content['max_score'] : null;
        $H5PContent->slug = !empty($content['slug']) ? $content['slug'] : '';
        $H5PContent->user_id = $content['user_id'];
        $H5PContent->content_create_mode = $adapter->getAdapterName();
        $H5PContent->is_published = $content['is_published'] ?? !$adapter->isUserPublishEnabled();
        $H5PContent->is_private = (bool) ($content['is_private'] ?? true);
        $H5PContent->is_draft =  $content['is_draft'] ?? 1;
        $H5PContent->language_iso_639_3 = $content['language_iso_639_3'] ?? null;

        $H5PContent->save();
        unset($metadata['title']);

        if (!empty($metadata)) {
            $metadata['content_id'] = $H5PContent->id;
            /** @var H5PContentsMetadata $H5PContentMetadata */
            $H5PContentMetadata = H5PContentsMetadata::make($metadata);
            $H5PContentMetadata->save();
        }

        return $H5PContent->id;
    }


    /**
     * Update old content.
     *
     * @param array $content
     *   An associative array containing:
     *   - id: The content id
     *   - params: The content in json format
     *   - library: An associative array containing:
     *     - libraryId: The id of the main library for this content
     * @param int $contentMainId
     *   Main id for the content if this is a system that supports versioning
     * TODO: Implement this for real....
     */
    public function updateContent($content, $contentMainId = null)
    {
        $metadataRaw = (array)$content['metadata'];
        $metadata = \H5PMetadata::toDBArray($metadataRaw, true);

        $H5PContent = H5PContent::find($content['id']);
        $H5PContent->title = !empty($metadata['title']) ? $metadata['title'] : $content['title'];
        $H5PContent->parameters = $content['params'];
        $H5PContent->filtered = '';
        $H5PContent->library_id = $content['library']['libraryId'];
        $H5PContent->embed_type = $content['embed_type'];
        $H5PContent->disable = $content['disable'];
        $H5PContent->slug = $content['slug'];
        $H5PContent->max_score = (int)$content['max_score'];
        $H5PContent->is_published = $content['is_published'];
        $H5PContent->is_draft = $content['is_draft'];
        $H5PContent->language_iso_639_3 = $content['language_iso_639_3'] ?? null;

        $H5PContent->update();
        unset($metadata['title']);

        if (!empty($metadata)) {
            /** @var H5PContentsMetadata $H5PContentMetadata */
            $H5PContentMetadata = H5PContentsMetadata::firstOrNew([
                'content_id' => $H5PContent->id
            ]);
            $H5PContentMetadata->fill($metadata);
            $H5PContentMetadata->save();
        }

        return $H5PContent;
    }

    /**
     * Resets marked user data for the given content.
     *
     * @param int $contentId
     * TODO: Implement this for real....
     */
    public function resetContentUserData($contentId)
    {
        return true;
    }

    /**
     * Save what libraries a library is dependending on
     *
     * @param array $dependencies
     *   List of dependencies as associative arrays containing:
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     * @param string $dependency_type
     *   What type of dependency this is, the following values are allowed:
     *   - editor
     *   - preloaded
     *   - dynamic
     * TODO: Implement this for real....
     */
    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
    {
        foreach ($dependencies as $dependency) {
            $libraries = H5PLibrary::fromLibrary([$dependency['machineName'],$dependency['majorVersion'],$dependency['minorVersion']])
                ->select('id')
                ->get()
                ->each(function ($library) use ($libraryId, $dependency_type) {
                    H5PLibraryLibrary::updateOrCreate([
                        'library_id' => $libraryId,
                        'required_library_id' => $library['id'],
                        'dependency_type' => $dependency_type,
                    ], [
                        'dependency_type' => $dependency_type,
                    ]);
                });
        }
    }

    /**
     * Give an H5P the same library dependencies as a given H5P
     *
     * @param int $contentId
     *   Id identifying the content
     * @param int $copyFromId
     *   Id identifying the content to be copied
     * @param int $contentMainId
     *   Main id for the content, typically used in frameworks
     *   That supports versioning. (In this case the content id will typically be
     *   the version id, and the contentMainId will be the frameworks content id
     */
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = null)
    {
        $sql = "INSERT INTO h5p_contents_libraries (content_id, library_id, dependency_type, weight, drop_css)
        SELECT ?, hcl.library_id, hcl.dependency_type, hcl.weight, hcl.drop_css
          FROM h5p_contents_libraries hcl
          WHERE hcl.content_id = ?";

        $this->db->prepare($sql)->execute([$contentId, $copyFromId]);
    }

    /**
     * Deletes content data
     *
     * @param int $contentId
     *   Id identifying the content
     */
    public function deleteContentData($contentId)
    {
        $this->runQuery("delete from h5p_contents where id=?", [$contentId]);
        $this->runQuery("delete from h5p_results where content_id=?", [$contentId]);
        $this->runQuery("delete from h5p_contents_user_data where content_id=?", [$contentId]);
    }

    /**
     * Delete what libraries a content item is using
     *
     * @param int $contentId
     *   Content Id of the content we'll be deleting library usage for
     */
    public function deleteLibraryUsage($contentId)
    {
        $sql = "delete from h5p_contents_libraries where content_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contentId]);
    }

    /**
     * Convert list of file paths to csv
     *
     * @param array $library
     *  Library data as found in library.json files
     * @param string $key
     *  Key that should be found in $libraryData
     * @param $pluck
     *  Value to pluck
     * @return string
     *  file paths separated by ', '
     */
    private function pathsToCsv($library, $key, $pluck)
    {
        return collect($library[$key] ?? [])->pluck($pluck)->implode(", ");
    }

    /**
     * Saves what libraries the content uses
     *
     * @param int $contentId
     *   Id identifying the content
     * @param array $librariesInUse
     *   List of libraries the content uses. Libraries consist of associative arrays with:
     *   - library: Associative array containing:
     *     - dropLibraryCss(optional): commasepareted list of machineNames
     *     - machineName: Machine name for the library
     *     - libraryId: Id of the library
     *   - type: The dependency type. Allowed values:
     *     - editor
     *     - dynamic
     *     - preloaded
     */
    public function saveLibraryUsage($contentId, $librariesInUse)
    {
        $dropLibraryCssList = [];

        foreach ($librariesInUse as $dependency) {
            if (!empty($dependency['library']['dropLibraryCss'])) {
                $dropLibraryCssList = array_merge(
                    $dropLibraryCssList,
                    explode(', ', $dependency['library']['dropLibraryCss'])
                );
            }
        }

        $dependencySQL = "insert into  h5p_contents_libraries values ( :content_id, :library_id, :dependency_type, :weight, :drop_css) ";
        $dependencyStmt = $this->db->prepare($dependencySQL);
        foreach ($librariesInUse as $dependency) {
            $dropCss = in_array($dependency['library']['machineName'], $dropLibraryCssList) ? 1 : 0;
            $params = [
                ':content_id' => $contentId,
                ':library_id' => $dependency['library']['libraryId'],
                ':dependency_type' => $dependency['type'],
                ':weight' => $dependency['weight'],
                ':drop_css' => $dropCss
            ];
            $dependencyStmt->execute($params);
        }
    }

    /**
     * Get number of content/nodes using a library, and the number of
     * dependencies to other libraries
     *
     * @param int $libraryId
     *   Library identifier
     * @return array
     *   Associative array containing:
     *   - content: Number of content using the library
     *   - libraries: Number of libraries depending on the library
     */
    public function getLibraryUsage($libraryId, $skipContent = false)
    {
        $usage = [
            'libraries' => H5PLibraryLibrary::where('required_library_id', $libraryId)->count(),
            'content' => null,
        ];
        if ($skipContent === false) {
            $usage['content'] = H5PContent::where('library_id', $libraryId)->count();
        }
        return $usage;
    }

    public function loadLibrary($machineName, $majorVersion, $minorVersion): array|false
    {
        $h5pLibrary = H5PLibrary::with(['libraries' => ['requiredLibrary']])
            ->where([
                'name' => $machineName,
                'major_version' => $majorVersion,
                'minor_version' => $minorVersion,
            ])
            ->orderBy('patch_version', 'desc')
            ->first();

        if (!$h5pLibrary instanceof H5PLibrary) {
            return false;
        }

        $library = [
            'libraryId' => $h5pLibrary->id,
            'title' => $h5pLibrary->title,
            'machineName' => $h5pLibrary->name,
            'majorVersion' => $h5pLibrary->major_version,
            'minorVersion' => $h5pLibrary->minor_version,
            'patchVersion' => $h5pLibrary->patch_version,
            'runnable' => $h5pLibrary->runnable,
            'fullscreen' => $h5pLibrary->fullscreen,
            'embedTypes' => $h5pLibrary->embed_types,
            'preloadedJs' => $h5pLibrary->preloaded_js,
            'preloadedCss' => $h5pLibrary->preloaded_css,
            'dropLibraryCss' => $h5pLibrary->drop_library_css,
            'semantics' => $h5pLibrary->semantics,
        ];

        foreach ($h5pLibrary->libraries as $dependency) {
            $library[$dependency->dependency_type . 'Dependencies'][] = [
                'machineName' => $dependency->requiredLibrary->name,
                'majorVersion' => $dependency->requiredLibrary->major_version,
                'minorVersion' => $dependency->requiredLibrary->minor_version,
            ];
        }

        return $library;
    }

    /**
     * Loads library semantics.
     *
     * @param string $machineName
     *   Machine name for the library
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     * @return string
     *   The library's semantics as json
     */
    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion)
    {
        $row = H5PLibrary::fromMachineName($machineName)
            ->version($majorVersion, $minorVersion)
            ->select('semantics')
            ->first();

        return $row['semantics'];
    }

    /**
     * Makes it possible to alter the semantics, adding custom fields, etc.
     *
     * @param array $semantics
     *   Associative array representing the semantics
     * @param string $machineName
     *   The library's machine name
     * @param int $majorVersion
     *   The library's major version
     * @param int $minorVersion
     *   The library's minor version
     */
    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
        $adapter = app(H5PAdapterInterface::class);
        $adapter->alterLibrarySemantics($semantics, $machineName, $majorVersion, $minorVersion);
    }

    /**
     * Delete all dependencies belonging to given library
     *
     * @param int $libraryId
     *   Library identifier
     */
    public function deleteLibraryDependencies($libraryId)
    {
        H5PLibraryLibrary::where('library_id', $libraryId)->delete();
    }

    /**
     * Start an atomic operation against the dependency storage
     * TODO: Implement this for real
     * TODO: Check Drupal source for what is supposed to happen, WP does not support this.
     */
    public function lockDependencyStorage()
    {
    }

    /**
     * Stops an atomic operation against the dependency storage
     * TODO: Implement this for real....
     * TODO: Check Drupal source for what is supposed to happen, WP does not support this.
     */
    public function unlockDependencyStorage()
    {
    }

    public function deleteLibrary($library): void
    {
        if (!is_object($library)) {
            throw new TypeError(sprintf('Expected object, %s given', get_debug_type($library)));
        }

        $libraryModel = H5PLibrary::findOrFail($library->id);
        $libraryModel->deleteOrFail();

        app(\H5PFileStorage::class)->deleteLibrary($libraryModel->getLibraryH5PFriendly());
    }

    /**
     * Load content.
     *
     * @param int $id
     *   Content identifier
     * @return array
     *   Associative array containing:
     *   - contentId: Identifier for the content
     *   - params: json content as string
     *   - embedType: csv of embed types
     *   - title: The contents title
     *   - language: Language code for the content
     *   - libraryId: Id for the main library
     *   - libraryName: The library machine name
     *   - libraryMajorVersion: The library's majorVersion
     *   - libraryMinorVersion: The library's minorVersion
     *   - libraryEmbedTypes: CSV of the main library's embed types
     *   - libraryFullscreen: 1 if fullscreen is supported. 0 otherwise.
     * TODO: Handle language
     */
    public function loadContent($id)
    {
        /** @var H5PContent $h5pcontent */
        $h5pcontent = H5PContent::with(['library', 'metadata'])
            ->findOrFail($id);

        $content = [
            'id' => $h5pcontent->id,
            'contentId' => $h5pcontent->id,
            'params' => $h5pcontent->parameters,
            'filtered' => $h5pcontent->filtered,
            'embedType' => $h5pcontent->embed_type,
            'title' => $h5pcontent->title,
            'disable' => $h5pcontent->disable,
            'user_id' => $h5pcontent->user_id,
            'slug' => $h5pcontent->slug,
            'libraryId' => $h5pcontent->library->id,
            'libraryName' => $h5pcontent->library->name,
            'libraryMajorVersion' => $h5pcontent->library->major_version,
            'libraryMinorVersion' => $h5pcontent->library->minor_version,
            'libraryEmbedTypes' => $h5pcontent->library->embed_types,
            'libraryFullscreen' => $h5pcontent->library->fullscreen,
            'language' => $h5pcontent->metadata->default_language ?? null,
            'max_score' => $h5pcontent->max_score,
            'created_at' => $h5pcontent->created_at,
            'updated_at' => $h5pcontent->updated_at,
        ];

        $content['metadata'] = $h5pcontent->getMetadataStructure();

        return $content;
    }

    /**
     * Load dependencies for the given content of the given type.
     *
     * @param int $id
     *   Content identifier
     * @param int $type
     *   Dependency types. Allowed values:
     *   - editor
     *   - preloaded
     *   - dynamic
     * @return array
     *   List of associative arrays containing:
     *   - libraryId: The id of the library if it is an existing library.
     *   - machineName: The library machineName
     *   - majorVersion: The library's majorVersion
     *   - minorVersion: The library's minorVersion
     *   - patchVersion: The library's patchVersion
     *   - preloadedJs(optional): comma separated string with js file paths
     *   - preloadedCss(optional): comma separated sting with css file paths
     *   - dropCss(optional): csv of machine names
     */
    public function loadContentDependencies($id, $type = null)
    {
        $allowedDependencyTypes = ['editor', 'preloaded', 'dynamic'];
        if ($type !== null && !in_array($type, $allowedDependencyTypes)) {
            throw new RuntimeException(__METHOD__ . ": invalid dependency type. Only editor, preloaded or dynamic is allowed");
        }
        $sql =
            "SELECT hl.id
              , hl.name AS machineName
              , hl.major_version AS majorVersion
              , hl.minor_version AS minorVersion
              , hl.patch_version AS patchVersion
              , hl.preloaded_css AS preloadedCss
              , hl.preloaded_js AS preloadedJs
              , hcl.drop_css AS dropCss
              , hcl.dependency_type AS dependencyType
        FROM h5p_contents_libraries hcl
        JOIN h5p_libraries hl ON hcl.library_id = hl.id
        WHERE hcl.content_id = ?";
        $queryArgs = [$id];

        if ($type !== null) {
            $sql .= " AND hcl.dependency_type = ?";
            $queryArgs[] = $type;
        }

        $sql .= " ORDER BY hcl.weight";

        $cstmt = $this->db->prepare($sql);
        $cstmt->execute($queryArgs);
        $content = $cstmt->fetchAll(PDO::FETCH_ASSOC);
        return $content;
    }

    /**
     * Get stored setting.
     *
     * @param string $name
     *   Identifier for the setting
     * @param string $default
     *   Optional default value if settings is not set
     * @return mixed
     *   Whatever has been stored as the setting
     */
    public function getOption($name, $default = null)
    {
        switch ($name) {
            case "export":
                return config('h5p.defaultExportOption');
            case "embed":
                /** @var H5PAdapterInterface $adapter */
                $adapter = app(H5PAdapterInterface::class);
                return $adapter->useEmbedLink();
            case 'enable_lrs_content_types':
                return true;
            case 'send_usage_statistics':
                return false;
            case 'hub_is_enabled':
                return config('h5p.isHubEnabled') || Request::is('admin/*');
            default:
                return app(H5POptionsCache::class)->get($name, $default);
        }
    }


    /**
     * Stores the given setting.
     * For example when did we last check h5p.org for updates to our libraries.
     *
     * @param string $name
     *   Identifier for the setting
     * @param mixed $value Data
     *   Whatever we want to store as the setting
     */
    public function setOption($name, $value)
    {
        H5POption::updateOrCreate(['option_name' => $name], ['option_value' => $value]);
    }

    /**
     * This will update selected fields on the given content.
     *
     * @param int $id Content identifier
     * @param array $fields Content fields, e.g. filtered or slug.
     * TODO: Implement this for real....
     */
    public function updateContentFields($id, $fields)
    {
        $content = H5PContent::findOrFail($id);
        $content->fill($fields);
        if ($content->isDirty([
            'filtered',
            'slug'
        ])) {
            return $content->save();
        }
        return true;
    }

    /**
     * Will clear filtered params for all the content that uses the specified
     * libraries. This means that the content dependencies will have to be rebuilt,
     * and the parameters refiltered.
     *
     * @param array $library_ids
     */
    public function clearFilteredParameters($library_ids)
    {
        if (!is_array($library_ids)) {
            $library_ids = [$library_ids];
        }
        H5PContent::whereIn('library_id', $library_ids)->update(['filtered' => '']);
    }

    /**
     * Get number of contents that has to get their content dependencies rebuilt
     * and parameters refiltered.
     *
     * @return int
     */
    public function getNumNotFiltered()
    {
        // Needs to be looked at. When the numper of H5Ps increase this query takes too long
        return H5PContent::where('filtered', '')->count();
    }

    /**
     * Get number of contents using library as main library.
     *
     * @param int $libraryId
     * @param array $skip
     * @return int
     */
    public function getNumContent($libraryId, $skip = null)
    {
        return H5PContent::where('library_id', $libraryId)->count();
    }

    /**
     * Determines if content slug is used.
     *
     * @param string $slug
     * @return boolean
     */
    public function isContentSlugAvailable($slug)
    {
        $sql = "select slug from h5p_contents where slug=?";
        $res = $this->db->prepare($sql)->execute([$slug])->fetch(PDO::FETCH_ASSOC);
        if (sizeof($res) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Cerpus functions
     */

    /**
     * @param $sql
     * @param array $params
     * @param bool $returnFirst
     */
    private function runQuery($sql, $params = [], $returnFirst = false)
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        if ($returnFirst === true) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $all;
    }

    public function getLibraryStats($type)
    {
        // TODO: implement this
        return [];
    }

    public function getNumAuthors()
    {
        // TODO: Implement getNumAuthors() method.
    }

    public function saveCachedAssets($key, $libraries)
    {
        foreach ($libraries as $library) {
            H5PLibrariesCachedAssets::create([
                'hash' => $key,
                'library_id' => $library['id']
            ]);
        }
    }

    public function deleteCachedAssets($library_id)
    {
        $cachedAssets = H5PLibrariesCachedAssets::where('library_id', $library_id)->get();
        $cachedAssets->each(function ($asset) {
            $asset->delete();
        });
        return $cachedAssets->pluck('hash')->toArray();
    }


    /**
     * Get the amount of content items associated to a library
     * @return array
     */
    public function getLibraryContentCount()
    {
        $libraries = H5PLibrary::all()
            ->filter(function ($library) {
                return $library->runnable != "0" && $library->contents()->count() > 0;
            })
            ->transform(function ($library) {
                $item = new \stdClass();
                $item->key = sprintf(
                    "%s %s.%s",
                    $library->name,
                    $library->major_version,
                    $library->minor_version
                );
                $item->count = $library->contents()->count();

                return $item;
            });

        $libraryCount = [];
        foreach ($libraries as $library) {
            $libraryCount[$library->key] = $library->count;
        }

        return $libraryCount;
    }

    /**
     * Will trigger after the export file is created.
     */
    public function afterExportCreated($content, $filename)
    {
        // TODO: Implement afterExportCreated() method.
    }

    public function hasPermission($permission, $id = null)
    {
        switch ($permission) {
            case \H5PPermission::DOWNLOAD_H5P:
            case \H5PPermission::EMBED_H5P:
                return false;

            case \H5PPermission::CREATE_RESTRICTED:
                return false;

            case \H5PPermission::INSTALL_RECOMMENDED:
            case \H5PPermission::UPDATE_LIBRARIES:
                return Request::is('admin/*');
        }
    }

    /**
     * Get URL to file in the specific library
     * @param string $libraryFolderName
     * @param string $fileName
     * @return string URL to file
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
        $storageInterface = app(CerpusStorageInterface::class);

        $path = implode("/", [
            'libraries',
            $libraryFolderName,
            $fileName
        ]);

        return $storageInterface->getFileUrl($path);
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     */
    public function replaceContentTypeCache($contentTypeCache)
    {
        DB::transaction(function () use ($contentTypeCache) {
            H5PLibrariesHubCache::where('owner', '<>', 'Cerpus')->delete();

            foreach ($contentTypeCache->contentTypes as $type) {
                H5PLibrariesHubCache::create([
                    'name' => $type->id,
                    'major_version' => $type->version->major,
                    'minor_version' => $type->version->minor,
                    'patch_version' => $type->version->patch,
                    'h5P_major_version' => $type->coreApiVersionNeeded->major,
                    'h5P_minor_version' => $type->coreApiVersionNeeded->minor,
                    'title' => $type->title,
                    'summary' => $type->summary,
                    'description' => $type->description,
                    'icon' => $type->icon,
                    'is_recommended' => $type->isRecommended,
                    'popularity' => $type->popularity,
                    'screenshots' => !empty($type->screenshots) ? json_encode($type->screenshots) : '',
                    'license' => json_encode($type->license),
                    'example' => $type->example ?? '',
                    'tutorial' => $type->tutorial ?? '',
                    'keywords' => !empty($type->keywords) ? json_encode($type->keywords) : '',
                    'categories' => json_encode($type->categories ?? []),
                    'owner' => $type->owner,
                ]);
            }
        });
    }

    /**
     * Load addon libraries
     *
     * @return array
     */
    public function loadAddons()
    {
        return H5PLibrary::make()->getAddons();
    }

    /**
     * Load config for libraries
     *
     * @param array $libraries
     * @return array
     */
    public function getLibraryConfig($libraries = null)
    {
        return [];
    }

    /**
     * Checks if the given library has a higher version.
     *
     * @param array $library
     * @return boolean
     */
    public function libraryHasUpgrade($library)
    {
        $h5pLibrary = H5PLibrary::fromLibrary($library)->first();
        return !is_null($h5pLibrary) && $h5pLibrary->isUpgradable();
    }

    public function replaceContentHubMetadataCache($metadata, $lang)
    {
        // H5P Content Hub is not in use
    }

    public function getContentHubMetadataCache($lang = 'en')
    {
        // H5P Content Hub is not in use, but return value to make PHPStan happy
        return new Stringable();
    }

    public function getContentHubMetadataChecked($lang = 'en')
    {
        // H5P Content Hub is not in use, but return value to make PHPStan happy
        return now()->toRfc7231String();
    }

    public function setContentHubMetadataChecked($time, $lang = 'en')
    {
        // H5P Content Hub is not in use, but return value to make PHPStan happy
        return true;
    }
}
