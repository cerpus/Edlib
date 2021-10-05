<?php

namespace App\Libraries\H5P;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Helper\UrlHelper;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\ContentTypeInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\SessionKeys;
use App\Traits\H5PBehaviorSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ViewConfig implements ConfigInterface
{
    use Config, H5PBehaviorSettings;

    public $id;
    protected $packageStructure;

    /** @var H5PAlterParametersSettingsDataObject */
    protected $alterParametersSettings;

    public function __construct(H5PAdapterInterface $adapter, \H5PCore $core)
    {
        $this->h5pCore = $core;
        $this->fileStorage = $core->fs;

        $adapter->setConfig($this);
        $this->adapter = $adapter;
    }

    private function init()
    {
        $this->initBehaviorSettings();
        $this->initConfig();
        $this->initContentConfig();
        $this->getDependentFiles();
        $this->addCustomScripts();
        $this->makePackageSpecificConfigModifications();
    }

    /**
     * @param mixed $id
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

    public function setAlterParametersSettings(H5PAlterParametersSettingsDataObject $settings)
    {
        $this->alterParametersSettings = $settings;
    }

    private function getDependentFiles()
    {
        $this->assets = $this->h5pCore->getDependenciesFiles($this->h5pCore->loadContentDependencies($this->id, "preloaded"));
        array_map(function ($type){
            return array_map(function ($file){
                if( !filter_var($file->path, FILTER_VALIDATE_URL)){
                    $file->path = $this->h5pCore->fs->getDisplayPath(false) . "/" . $file->path;
                }
                return $file;
            }, $type);
        }, $this->assets);
        $this->addCustomViewCss();
    }

    private function addCustomViewCss()
    {
        array_map(function ($css){
            $this->addAsset('styles', $this->getAssetUrl(null, $css));
        }, $this->adapter->getCustomViewCss());
    }

    private function addCustomScripts()
    {
        return array_map(function ($script){
            $this->addAsset('scripts', (object)[
                'path' => $script,
                'version' => null,
            ]);
        }, $this->adapter->getCustomViewScripts());
    }

    private function initContentConfig()
    {
        $core = $this->h5pCore;
        $pdo = $this->h5plugin->getPdo();

        $content = $this->getContent();
        if (!array_key_exists("slug", $content)) {
            $content['slug'] = \H5PCore::slugify($content['title']);
        }

        // Getting author's user id
        $author_id = (int)(is_array($content) ? $content['user_id'] : $content->user_id);

        $parameters = $this->behaviorSettings($content['library']['name'], $this->adapter->alterParameters($core->filterParameters($content), $this->alterParametersSettings));

        $contentConfig = new \stdClass();
        $contentConfig->library = $core::libraryToString($content['library']);
        $contentConfig->jsonContent = $parameters;
        $contentConfig->fullScreen = $content['library']['fullscreen'];
        $contentConfig->exportUrl = route('content-download', ['h5p' => $content['id']]);
        $contentConfig->embedCode = Session::get(SessionKeys::EXT_CONTEXT_EMBED, '');
        $contentConfig->resizeCode = $contentConfig->embedCode
            ? '<script src="https://h5p.org/sites/all/modules/h5p/library/js/h5p-resizer.js"></script>'
            : '';
        $contentConfig->url = UrlHelper::getCurrentFullUrl();
        $contentConfig->title = $content['title'];
        $contentConfig->metadata = $content['metadata'];
        $disable = config('h5p.overrideDisableSetting');
        $contentConfig->displayOptions = $core->getDisplayOptionsForView($disable === false ? $content['disable'] : $disable, $author_id);
        $state = false;
        if( is_null($this->behaviorSettings) || $this->behaviorSettings->includeAnswers === true){
            $progress = new H5PProgress($pdo, $this->userId);
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
        if( !is_null($this->behaviorSettings)){
            try{
                /** @var ContentTypeInterface $package */
                $package = H5PPackageProvider::make($library, $parameters);
                $parameters = $package->applyBehaviorSettings($this->behaviorSettings);
                $css = $package->getCss(false);
                if( !empty($css)){
                    array_walk($css, function ($cssString){
                        $this->addCss($cssString);
                    });
                }
            } catch (UnknownH5PPackageException $exception){
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
