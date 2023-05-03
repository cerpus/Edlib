<?php

namespace App\Libraries\H5P;

use App\Apis\ResourceApiService;
use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Helper\UrlHelper;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\SessionKeys;
use App\Traits\H5PBehaviorSettings;
use Illuminate\Support\Facades\DB;

class ViewConfig implements ConfigInterface
{
    use Config;
    use H5PBehaviorSettings;

    public $id;
    protected $packageStructure;

    /** @var H5PAlterParametersSettingsDataObject */
    protected $alterParametersSettings;
    private ResourceApiService $resourceApiService;
    private ?string $edlibUrl = null;

    public function __construct(
        H5PAdapterInterface $adapter,
        \H5PCore $core,
        ResourceApiService $resourceApiService
    ) {
        $this->h5pCore = $core;
        $this->fileStorage = $core->fs;

        $adapter->setConfig($this);
        $this->adapter = $adapter;
        $this->resourceApiService = $resourceApiService;
    }

    private function init()
    {
        $this->initBehaviorSettings();
        $this->initConfig();
        $this->initContentConfig();
        $this->getDependentFiles();
        $this->addCustomScripts();
        $this->initDocumentUrl();
        $this->makePackageSpecificConfigModifications();
    }

    /**
     * @return ViewConfig
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getConfig()
    {
        $this->init();
        return $this->config;
    }

    public function initDocumentUrl()
    {
        $edlibUrl = $this->getEdlibUrl();

        if ($edlibUrl) {
            $this->config->documentUrl = $edlibUrl;
        }
    }

    public function getEdlibUrl()
    {
        if ($this->edlibUrl) {
            return $this->edlibUrl;
        }

        $resource = $this->resourceApiService->getResourceFromExternalReference('contentauthor', $this->id);
        $edlibEmbedPath = config('edlib.embedPath');

        if (!$edlibEmbedPath) {
            return null;
        }

        $this->edlibUrl = str_replace('<resourceId>', $resource->id, $edlibEmbedPath);

        return $this->edlibUrl;
    }

    private function getEmbedCode()
    {
        $edlibUrl = $this->getEdlibUrl();

        if (!$edlibUrl) {
            return '';
        }

        $resource = $this->resourceApiService->getResourceFromExternalReference('contentauthor', $this->id);
        return sprintf(
            '<iframe src="%s" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen" allow="geolocation *; microphone *; camera *; midi *; encrypted-media *" title="%s"></iframe>',
            htmlspecialchars($edlibUrl, ENT_QUOTES),
            htmlspecialchars($resource->title ?? '', ENT_QUOTES)
        );
    }

    public function setAlterParametersSettings(H5PAlterParametersSettingsDataObject $settings)
    {
        $this->alterParametersSettings = $settings;
    }

    private function getDependentFiles()
    {
        $this->assets = $this->h5pCore->getDependenciesFiles($this->h5pCore->loadContentDependencies($this->id, "preloaded"));
        array_map(function ($type) {
            return array_map(function ($file) {
                if (isset($file->url)) {
                    $file->path = $file->url;
                } elseif (!filter_var($file->path, FILTER_VALIDATE_URL)) {
                    $file->path = $this->h5pCore->fs->getDisplayPath(false) . "/" . $file->path;
                }
                return $file;
            }, $type);
        }, $this->assets);
        $this->addCustomViewCss();
    }

    private function addCustomViewCss()
    {
        array_map(function ($css) {
            $this->addAsset('styles', $this->getAssetUrl(null, $css));
        }, $this->adapter->getCustomViewCss());
    }

    private function addCustomScripts()
    {
        return array_map(function ($script) {
            $this->addAsset('scripts', (object)[
                'path' => $script,
                'version' => null,
            ]);
        }, $this->adapter->getCustomViewScripts());
    }

    private function initContentConfig()
    {
        $core = $this->h5pCore;

        $content = $this->getContent();
        if (!array_key_exists("slug", $content)) {
            $content['slug'] = \H5PCore::slugify($content['title']);
        }

        // Getting author's user id
        $author_id = (int)(is_array($content) ? $content['user_id'] : $content->user_id);

        $parameters = $this->behaviorSettings($content['library']['name'], $this->adapter->alterParameters($core->filterParameters($content), $this->alterParametersSettings));

        $contentConfig = new \stdClass();
        $contentConfig->library = $content['libraryFullVersionName'];
        $contentConfig->jsonContent = $parameters;
        $contentConfig->fullScreen = $content['library']['fullscreen'];
        $contentConfig->exportUrl = route('content-download', ['h5p' => $content['id']]);
        $contentConfig->embedCode = $this->getEmbedCode();
        $contentConfig->resizeCode = $contentConfig->embedCode
            ? sprintf('<script src="%s"></script>', htmlspecialchars(url('/h5p-php-library/js/h5p-resizer.js')))
            : '';
        $contentConfig->url = UrlHelper::getCurrentFullUrl();
        $contentConfig->title = $content['title'];
        $contentConfig->metadata = $content['metadata'];
        $disable = config('h5p.overrideDisableSetting');
        $contentConfig->displayOptions = $core->getDisplayOptionsForView($disable === false ? $content['disable'] : $disable, $author_id);
        $state = false;
        if (is_null($this->behaviorSettings) || $this->behaviorSettings->includeAnswers === true) {
            $progress = new H5PProgress(DB::connection()->getPdo(), $this->userId);
            $state = $progress->getState($content['id'], $this->context);
        }
        $contentConfig->contentUserData = $state !== false ? $state : ['state' => false];

        $configName = 'cid-' . $content['id'];

        $contentConfigs = new \stdClass();
        $contentConfigs->$configName = $contentConfig;

        $this->config->contents = $contentConfigs;
    }

    private function behaviorSettings($library, $parameters)
    {
        if (!is_null($this->behaviorSettings)) {
            try {
                /** @var ContentTypeInterface $package */
                $package = H5PPackageProvider::make($library, $parameters);
                $parameters = $package->applyBehaviorSettings($this->behaviorSettings);
                $css = $package->getCss(false);
                if (!empty($css)) {
                    array_walk($css, function ($cssString) {
                        $this->addCss($cssString);
                    });
                }
            } catch (UnknownH5PPackageException $exception) {
                $this->packageStructure = json_decode($parameters);
                $parameters = $this->applyBehaviorSettings($this->behaviorSettings);
            }
        }
        return $parameters;
    }

    private function initBehaviorSettings()
    {
        $this->behaviorSettings = \Session::get(SessionKeys::EXT_BEHAVIOR_SETTINGS);
    }

    private function makePackageSpecificConfigModifications(): void
    {
        try {
            $h5pPackage = H5PPackageProvider::make($this->content["library"]["name"]);
            $this->config = $h5pPackage->alterConfig($this->config);
        } catch (UnknownH5PPackageException $e) {
        }
    }
}
