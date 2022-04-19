<?php

namespace App\Libraries\H5P;

use App\H5PLibrary;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use H5PCore;
use H5peditor;
use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\SessionKeys;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class AjaxRequest extends \H5PEditorEndpoints
{

    private $returnType;

    const CONTENT_UPGRADE_PROCESS = 'content_upgrade_progress';
    const CONTENT_SETFINISHED = 'setFinished';
    const CONTENTS_USER_DATA = 'contents_user_data';
    const H5P_BEHAVIOR_SETTINGS = 'cache/%s.css';
    const H5P_IMAGE_MANIPULATION = 'imageManipulation';

    const LIBRARY_REBUILD = 'rebuild';

    public function __construct(
        private H5PCore $core,
        private H5peditor $editor,
        private ContentAuthorStorage $contentAuthorStorage
    ) {
    }

    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * @param Request $request
     * @return array|bool|mixed|\stdClass|void
     * @throws \Exception
     */
    public function handleAjaxRequest(Request $request)
    {
        switch (filter_var($request->get("action"), FILTER_SANITIZE_STRING)) {
            case self::CONTENT_SETFINISHED:
            case self::CONTENTS_USER_DATA:
                /** @var H5PProgress $progress */
                $progress = app(H5PProgress::class, [DB::connection()->getPdo(), Session::get('userId', false)]);
                return $progress->storeProgress($request);
                break;
            case self::FILES:
                $this->returnType = "json";

                $contentId = filter_input(INPUT_POST, 'contentId', FILTER_SANITIZE_NUMBER_INT);

                $this->editor->ajax->action(\H5PEditorEndpoints::FILES, null, $contentId);
                exit;
                break;
            case self::LIBRARIES:
                $this->returnType = "json";
                $name = filter_input(INPUT_GET, 'machineName', FILTER_SANITIZE_STRING);
                $major_version = filter_input(INPUT_GET, 'majorVersion', FILTER_SANITIZE_NUMBER_INT);
                $minor_version = filter_input(INPUT_GET, 'minorVersion', FILTER_SANITIZE_NUMBER_INT);

                if ($name) {
                    $libraryData = $this->editor->getLibraryData($name, $major_version, $minor_version,
                        $request->get('language'),
                        $this->core->fs->getAjaxPath(),
                        null,
                        $request->get('default-language'));
                    $settings = $this->handleEditorBehaviorSettings($request, $name);
                    if (!empty($settings) && !empty($settings['file']) && isset($libraryData->css) && is_array($libraryData->css)) {
                        array_push($libraryData->css, $settings['file']);
                    }
                    return !is_string($libraryData) ? $libraryData : json_decode($libraryData);
                } else {
                    $libraries = $this->editor->getLibraries();
                    return !is_string($libraries) ? $libraries : json_decode($libraries);
                }
                break;
            case self::CONTENT_UPGRADE_PROCESS:
                $libraryId = $_POST["libraryId"];
                $token = $_POST["token"];
                echo 'libraryId: ' . $libraryId . '<br>token: ' . $token;
                echo $this->ajax_upgrade_progress();
                break;
            case self::CONTENT_TYPE_CACHE:
                return array(
                    'outdated' => false,
                    'libraries' => $this->editor->getLatestGlobalLibrariesData(),
                    'recentlyUsed' => $this->editor->ajaxInterface->getAuthorsRecentlyUsedLibraries(),
                    'apiVersion' => array(
                        'major' => H5PCore::$coreApi['majorVersion'],
                        'minor' => H5PCore::$coreApi['minorVersion']
                    ),
                    'details' => $this->core->h5pF->getMessages('info')
                );
                break;
            case self::LIBRARY_UPLOAD:
                return $this->libraryUpload($request);
                break;
            case self::LIBRARY_INSTALL:
                set_time_limit(60);
                return $this->libraryInstall($request->bearerToken(), $request->input('machineName'));
            case self::TRANSLATIONS:
                $this->returnType = "json";
                return $this->getTranslations($request);
            case self::FILTER:
                $this->returnType = "json";
                $isLoggedIn = $request->session()->get('authId');
                if (!$isLoggedIn) {
                    throw new \Exception("Not logged in");
                }
                return $this->filter($request->get('libraryParameters'));
            case self::LIBRARY_REBUILD:
                /** @var \H5PEditorAjaxInterface $editorAjax */
                $editorAjax = resolve(EditorAjax::class);
                $canRebuild = $this->core->mayUpdateLibraries();
                if (!$canRebuild || !$editorAjax->validateEditorToken($request->bearerToken())) {
                    throw new \Exception("Not logged in");
                }
                $library = $request->input('libraryId');
                return $this->libraryRebuild(H5PLibrary::findOrFail($library));
            case self::H5P_IMAGE_MANIPULATION:
                $imageId = $request->get('imageId');

                /** @var H5PImageAdapterInterface $imageAdapter */
                $imageAdapter = app(H5PImageAdapterInterface::class);
                return $imageAdapter->getImageUrlFromId($imageId, $request->all(), false);
            default:
                throw new \Exception("Unknown action: '" . $request->get('action') . "'");
        }
    }

    /**
     * Handles uploading libraries so they are ready to be modified or directly saved.
     *
     * Validates and saves any dependencies, then exposes content to the editor.
     *
     * @param {Request} $request Content id of library
     */
    private function libraryUpload(Request $request)
    {
        // Verify h5p upload
        if (!$request->hasFile('h5p')) {
            H5PCore::ajaxError($this->core->h5pF->t('Could not get posted H5P.'), 'NO_CONTENT_TYPE');
            exit;
        }

        $originalAjax = $this->editor->ajax;
        $originalAjax->action(self::LIBRARY_UPLOAD, $request->bearerToken(), $request->file('h5p')->getRealPath(), "0");
    }

    private function libraryInstall($token, $library)
    {
        $originalAjax = $this->editor->ajax;
        $originalAjax->action(self::LIBRARY_INSTALL, $token, $library);
    }

    private function handleEditorBehaviorSettings(Request $request, $library): array
    {
        $settings = $request->session()->get(sprintf(SessionKeys::EXT_EDITOR_BEHAVIOR_SETTINGS, $request->get('redirectToken')));
        if (empty($settings)) {
            return [];
        }

        try {
            /** @var ContentTypeInterface $package */
            $package = H5PPackageProvider::make($library);
            $package->applyEditorBehaviorSettings($settings);
            $styles = $package->getCSS(true);
        } catch (UnknownH5PPackageException $exception) {
            $editorConfig = resolve(EditorConfig::class);
            $editorConfig->applyEditorBehaviorSettings($settings);
            $styles = $editorConfig->getCSS(true);
        }

        $fileName = sprintf(self::H5P_BEHAVIOR_SETTINGS, md5($library . '|' . $styles));

        if (!$this->contentAuthorStorage->getBucketDisk()->has($fileName)) {
            $this->contentAuthorStorage->getBucketDisk()->put($fileName, $styles);
        }

        return [
            'styles' => $styles,
            'file' => $this->contentAuthorStorage->getAssetUrl($fileName),
        ];

    }

    private function getTranslations(Request $request)
    {
        $languageCode = $request->get('language');
        $libraries = $request->get('libraries', []);
        return ['success' => true, 'data' => $this->editor->getTranslations($libraries, $languageCode)];
    }

    /**
     * End-point for filter parameter values according to semantics.
     *
     * @param {string} $libraryParameters
     */
    private function filter($libraryParameters)
    {
        $libraryParameters = json_decode($libraryParameters);
        if (!$libraryParameters) {
            H5PCore::ajaxError($this->core->h5pF->t('Could not parse post data.'), 'NO_LIBRARY_PARAMETERS');
            exit;
        }
        $validator = new \H5PContentValidator($this->core->h5pF, $this->core);
        $validator->validateLibrary($libraryParameters, (object)array('options' => array($libraryParameters->library)));
        return [
            'success' => true,
            'data' => $libraryParameters
        ];
    }

    private function libraryRebuild(H5PLibrary $H5PLibrary)
    {
        $framework = $this->core->h5pF;

        $libraries = collect();
        $this->getLibraryDetails($H5PLibrary, $libraries)
            ->each(function ($library) use ($framework) {
                $framework->deleteLibraryDependencies($library['libraryId']);

                // Insert the different new ones
                if (isset($library['preloadedDependencies'])) {
                    $framework->saveLibraryDependencies($library['libraryId'], $library['preloadedDependencies'], 'preloaded');
                }
                if (isset($library['dynamicDependencies'])) {
                    $framework->saveLibraryDependencies($library['libraryId'], $library['dynamicDependencies'], 'dynamic');
                }
                if (isset($library['editorDependencies'])) {
                    $framework->saveLibraryDependencies($library['libraryId'], $library['editorDependencies'], 'editor');
                }
            });

        return [
            'success' => true,
            'message' => "Library rebuild",
        ];
    }

    private function getLibraryDetails(H5PLibrary $H5PLibrary, $affectedLibraries)
    {
        $validator = resolve(\H5PValidator::class);
        $h5pDataFolderName = $H5PLibrary->getLibraryString(true);
        $tmpLibrariesRelative = 'libraries';
        $tmpLibraryRelative = 'libraries/' . $h5pDataFolderName;
        // Download files from bucket to tmp folder
        $this->contentAuthorStorage->copyFolder(
            $this->contentAuthorStorage->getBucketDisk(),
            $this->contentAuthorStorage->getH5pTmpDisk(),
            $tmpLibraryRelative,
            $tmpLibraryRelative
        );
        $tmpLibraries = $this->core->h5pF->getH5pPath($tmpLibrariesRelative);
        $tmpLibraryFolder = $this->core->h5pF->getH5pPath($tmpLibraryRelative);
        $libraryData = $validator->getLibraryData($H5PLibrary->getLibraryString(true), $tmpLibraryFolder, $tmpLibraries);
        $libraryData['libraryId'] = $H5PLibrary->id;

        if (!$affectedLibraries->has($H5PLibrary->getLibraryString())) {
            $affectedLibraries->put($H5PLibrary->getLibraryString(), $libraryData);
        }
        foreach (['preloadedDependencies', 'dynamicDependencies', 'editorDependencies'] as $value) {
            if (!empty($libraryData[$value])) {
                foreach ($libraryData[$value] as $library) {
                    /** @var H5PLibrary $dependentLibrary */
                    $dependentLibrary = H5PLibrary::fromLibrary($library)->first();
                    if (!$affectedLibraries->has($dependentLibrary->getLibraryString())) {
                        $affectedLibraries = $this->getLibraryDetails($dependentLibrary, $affectedLibraries);
                    }
                }
            }
        }
        return $affectedLibraries;
    }
}
