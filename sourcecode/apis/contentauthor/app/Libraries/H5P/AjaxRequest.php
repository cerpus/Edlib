<?php

namespace App\Libraries\H5P;

use App\AuditLog;
use App\H5PLibrary;
use App\Libraries\ContentAuthorStorage;
use App\Libraries\H5P\Image\NdlaImageAdapter;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use H5PContentValidator;
use H5PCore;
use H5peditor;
use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\SessionKeys;
use H5PEditorAjaxInterface;
use H5PEditorEndpoints;
use H5PValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * @todo Split stuff into separate controllers
 * @todo Investigate and remove exit() statements
 */
class AjaxRequest
{
    public const H5P_IMAGE_MANIPULATION = 'imageManipulation';
    public const LIBRARY_REBUILD = 'rebuild';

    private string|null $returnType = null;

    public function __construct(
        private readonly H5PCore $core,
        private readonly H5peditor $editor,
        private readonly ContentAuthorStorage $contentAuthorStorage,
    ) {}

    public function getReturnType(): string|null
    {
        return $this->returnType;
    }

    public function handleAjaxRequest(Request $request): mixed
    {
        $action = $request->input('action');

        switch ($action) {
            case H5PEditorEndpoints::FILES:
                return $this->files($request);

            case H5PEditorEndpoints::LIBRARIES:
                return $this->libraries($request);

            case H5PEditorEndpoints::CONTENT_TYPE_CACHE:
                return $this->contentTypeCache();

            case H5PEditorEndpoints::LIBRARY_UPLOAD:
                $this->libraryUpload($request);
                return null;

            case H5PEditorEndpoints::LIBRARY_INSTALL:
                $this->libraryInstall($request->bearerToken(), $request->input('machineName'));
                return null;

            case H5PEditorEndpoints::TRANSLATIONS:
                return $this->getTranslations($request);

            case H5PEditorEndpoints::FILTER:
                return $this->filter($request);

            case H5PEditorEndpoints::CONTENT_HUB_METADATA_CACHE:
                return $this->contentTypeMetadataCache();

            case self::LIBRARY_REBUILD:
                return $this->libraryRebuild($request);

            case self::H5P_IMAGE_MANIPULATION:
                return $this->imageManipulation($request);

            default:
                throw new NotFoundHttpException("Unknown action: '$action'");
        }
    }

    private function files(Request $request): never
    {
        $this->returnType = "json";

        $contentId = (int) $request->input('contentId');

        $this->editor->ajax->action(H5PEditorEndpoints::FILES, null, $contentId);
        exit;
    }

    private function libraries(Request $request): mixed
    {
        $this->returnType = "json";
        $name = $request->input('machineName');
        $major_version = $request->input('majorVersion');
        $minor_version = $request->input('minorVersion');

        if ($name) {
            $libraryData = $this->editor->getLibraryData(
                $name,
                $major_version,
                $minor_version,
                $request->get('language'),
                app(CerpusStorageInterface::class)->getAjaxPath(),
                null,
                $request->get('default-language'),
            );
            $settings = $this->handleEditorBehaviorSettings($request, $name);
            if (!empty($settings['file']) && is_array($libraryData->css ?? null)) {
                $libraryData->css[] = $settings['file'];
            }
            return !is_string($libraryData) ? $libraryData : json_decode($libraryData);
        } else {
            $libraries = $this->editor->getLibraries();
            return !is_string($libraries) ? $libraries : json_decode($libraries);
        }
    }

    /**
     * The filter options for the H5P Hub option "Get shared Content"
     * This feature not enabled in Edlib, but the H5P Hub requests the data anyway
     */
    private function contentTypeMetadataCache(): array
    {
        $this->returnType = "json";
        return [
            'success' => true,
            'data' => [
                'levels' => [],
                'languages' => [],
                'licenses' => [],
                'disciplines' => [],
            ],
        ];
    }

    private function contentTypeCache(): array
    {
        return [
            'outdated' => false,
            'libraries' => $this->editor->getLatestGlobalLibrariesData(),
            'recentlyUsed' => $this->editor->ajaxInterface->getAuthorsRecentlyUsedLibraries(),
            'apiVersion' => [
                'major' => H5PCore::$coreApi['majorVersion'],
                'minor' => H5PCore::$coreApi['minorVersion'],
            ],
            'details' => $this->core->h5pF->getMessages('info'),
        ];
    }

    /**
     * Handles uploading libraries so they are ready to be modified or directly saved.
     *
     * Validates and saves any dependencies, then exposes content to the editor.
     */
    private function libraryUpload(Request $request): void
    {
        // Verify h5p upload
        if (!$request->hasFile('h5p')) {
            H5PCore::ajaxError($this->core->h5pF->t('Could not get posted H5P.'), 'NO_CONTENT_TYPE');
            exit;
        }
        $file = $request->file('h5p');
        AuditLog::log(
            'Upload content from front',
            json_encode([
                'file' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'target' => $file->getPathname(),
                ],
            ]),
        );

        $this->editor->ajax->action(H5PEditorEndpoints::LIBRARY_UPLOAD, $request->bearerToken(), $file->getRealPath(), "0");
    }

    private function libraryInstall($token, $library): void
    {
        set_time_limit(60);
        AuditLog::log(
            'Install library from h5p.org',
            json_encode([
                'library' => $library,
            ]),
        );

        $this->editor->ajax->action(H5PEditorEndpoints::LIBRARY_INSTALL, $token, $library);
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
        } catch (UnknownH5PPackageException) {
            $editorConfig = resolve(H5PEditConfig::class);
            $editorConfig->applyEditorBehaviorSettings($settings);
            $styles = $editorConfig->getCSS(true);
        }

        $fileName = sprintf('cache/%s.css', md5($library . '|' . $styles));

        if (!Storage::disk()->has($fileName)) {
            Storage::disk()->put($fileName, $styles);
        }

        return [
            'styles' => $styles,
            'file' => Storage::disk()->url($fileName),
        ];
    }

    private function getTranslations(Request $request): array
    {
        $this->returnType = "json";
        $languageCode = $request->get('language');
        $libraries = $request->get('libraries', []);
        return [
            'success' => true,
            'data' => $this->editor->getTranslations($libraries, $languageCode),
        ];
    }

    /**
     * End-point for filter parameter values according to semantics.
     */
    private function filter(Request $request)
    {
        if (!$request->session()->get('authId')) {
            throw new UnauthorizedHttpException("Not logged in");
        }
        $this->returnType = "json";
        $libraryParameters = json_decode($request->get('libraryParameters'));
        if (!$libraryParameters) {
            H5PCore::ajaxError($this->core->h5pF->t('Could not parse post data.'), 'NO_LIBRARY_PARAMETERS');
            exit;
        }
        $validator = new H5PContentValidator($this->core->h5pF, $this->core);
        $validator->validateLibrary($libraryParameters, (object) ['options' => [$libraryParameters->library]]);
        return [
            'success' => true,
            'data' => $libraryParameters,
        ];
    }

    private function libraryRebuild(Request $request): array
    {
        /** @var H5PEditorAjaxInterface $editorAjax */
        $editorAjax = resolve(EditorAjax::class);
        $canRebuild = $this->core->mayUpdateLibraries();
        if (!$canRebuild || !$editorAjax->validateEditorToken($request->bearerToken())) {
            throw new UnauthorizedHttpException("Not logged in");
        }

        /** @var H5PLibrary $H5PLibrary */
        $H5PLibrary = H5PLibrary::findOrFail($request->input('libraryId'));
        $framework = $this->core->h5pF;

        $libraries = collect();
        $this->getLibraryDetails($H5PLibrary, $libraries);
        if ($libraries->has($H5PLibrary->getLibraryString(false))) {
            $libraryData = $libraries->get($H5PLibrary->getLibraryString(false));
            if (array_key_exists('semantics', $libraryData)) {
                $H5PLibrary->semantics = $libraryData['semantics'];
                $H5PLibrary->save();
            }
        }

        $libraries->each(function ($library) use ($framework) {
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

    private function getLibraryDetails(H5PLibrary $H5PLibrary, Collection $affectedLibraries): Collection
    {
        /** @var H5PValidator $validator */
        $validator = resolve(H5PValidator::class);
        $h5pDataFolderName = $H5PLibrary->getFolderName();
        $tmpLibrariesRelative = 'libraries';
        $tmpLibraryRelative = 'libraries/' . $h5pDataFolderName;
        // Download files from bucket to tmp folder
        $this->contentAuthorStorage->copyFolder(
            Storage::disk(),
            Storage::disk('h5pTmp'),
            $tmpLibraryRelative,
            $tmpLibraryRelative,
        );
        $tmpLibraries = $this->core->h5pF->getH5pPath($tmpLibrariesRelative);
        $tmpLibraryFolder = $this->core->h5pF->getH5pPath($tmpLibraryRelative);
        $libraryData = $validator->getLibraryData($H5PLibrary->getFolderName(), $tmpLibraryFolder, $tmpLibraries);
        $libraryData['libraryId'] = $H5PLibrary->id;

        if (!$affectedLibraries->has($H5PLibrary->getLibraryString(false))) {
            $affectedLibraries->put($H5PLibrary->getLibraryString(false), $libraryData);
        }
        foreach (['preloadedDependencies', 'dynamicDependencies', 'editorDependencies'] as $value) {
            if (!empty($libraryData[$value])) {
                foreach ($libraryData[$value] as $library) {
                    /** @var H5PLibrary $dependentLibrary */
                    $dependentLibrary = H5PLibrary::fromLibrary($library)->first();
                    if (!$affectedLibraries->has($dependentLibrary->getLibraryString(false))) {
                        $affectedLibraries = $this->getLibraryDetails($dependentLibrary, $affectedLibraries);
                    }
                }
            }
        }

        return $affectedLibraries;
    }

    private function imageManipulation(Request $request): string
    {
        $imageId = $request->get('imageId');

        $imageAdapter = app(H5PImageInterface::class);
        if ($imageAdapter instanceof NdlaImageAdapter) {
            return $imageAdapter->getImageUrlFromId($imageId, $request->all(), false);
        }

        throw new LogicException('not implemented');
    }
}
