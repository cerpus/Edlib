<?php

namespace App\Libraries\H5P\Traits;

use App\H5PLibrary;
use App\Libraries\H5P\Interfaces\CerpusStorageInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\LtiToH5PLanguage;
use Exception;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use stdClass;

use function request;

trait Config
{
    public $config;
    public $content;
    /** @var \H5PCore */
    public $h5pCore;
    public $assets;
    public $context;
    public $preview;
    /** @var  H5PLibrary */
    public $library;

    private $userId;
    private $userName;
    private $name;
    private $email;

    private $displayH5PHub = false;

    /** @var H5PAdapterInterface */
    protected $adapter;

    /** @var CerpusStorageInterface */
    protected $fileStorage;

    /**
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return $this
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
        return $this;
    }

    /**
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
        return $this;
    }

    /**
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return $this
     */
    public function setDisplayH5PHub(bool $displayH5PHub): self
    {
        $this->displayH5PHub = $displayH5PHub;
        return $this;
    }

    /**
     * @return $this
     */
    public function setLibrary(H5PLibrary $library)
    {
        $this->library = $library;
        return $this;
    }

    /**
     * @param  array  $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getScriptAssets()
    {
        return $this->getAssets("scripts");
    }

    public function getStyleAssets()
    {
        return $this->getAssets("styles");
    }

    private function getAssets($type)
    {
        return is_array($this->assets) && array_key_exists($type, $this->assets) ? $this->assets[$type] : [];
    }

    public function addAsset($type, $asset)
    {
        $this->assets[$type][] = $asset;
    }

    protected function getAssetUrl($h5pType, $script)
    {
        $prefix = "";
        switch ($h5pType) {
            case "core":
                $prefix = "/h5p-php-library/";
                break;
            case "editor":
                $prefix = "/h5p-editor-php-library/";
                break;
        }
        return $prefix . $script . "?ver=" . $this->getCacheBustingVersionString();
    }

    protected function addCoreAssets()
    {
        if (!isset($this->config)) {
            $this->config = (object) [];
        }

        $core = $this->h5pCore;
        $coreAssets = array_merge($core::$scripts, ["js/h5p-display-options.js"]);
        foreach ($coreAssets as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            if (!in_array($scriptPath, $this->getScriptAssets())) {
                $this->addAsset("scripts", $scriptPath);
                $this->config->core['scripts'][] = $scriptPath;
            }
        }

        foreach ($core::$styles as $style) {
            $stylePath = $this->getAssetUrl("core", $style);
            if (!in_array($stylePath, $this->getStyleAssets())) {
                $this->addAsset("styles", $stylePath);
                $this->config->core['styles'][] = $stylePath;
            }
        }
    }

    /**
     * @return array|null
     */
    public function getContent()
    {
        return $this->content;
    }

    public function getContentUserDataUrl()
    {
        return $this->preview !== true ? '/api/progress?action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId&context=' . $this->context : '/api/progress?action=h5p_preview&c=1';
    }

    public function getSetFinishedUrl()
    {
        return $this->preview !== true ? '/api/progress?action=h5p_setFinished' : '/api/progress?action=h5p_preview&f=1';
    }

    public function canGiveScore(): bool
    {
        return $this->content['max_score'] ?? false;
    }

    private function getDisplayName()
    {
        if (!empty($this->name)) {
            return $this->name;
        } elseif (!empty($this->userName)) {
            return $this->userName;
        } elseif (!empty($this->userId)) {
            return $this->userId;
        } else {
            return trans('h5p-editor.anonymous');
        }
    }

    /**
     * @return stdClass
     * @throws Exception
     */
    public function initConfig()
    {
        if (!is_null($this->config)) {
            return $this->config;
        }

        $content = $this->getContent();
        if (!empty($content)) {
            $this->setLibrary(H5PLibrary::find($content['library']['id']));
        }

        $user = new stdClass();
        $user->name = $this->getDisplayName();
        $user->mail = $this->email;
        $config = new stdClass();
        $config->baseUrl = request()->getSchemeAndHttpHost() . request()->getBasePath();
        $config->url = $this->fileStorage->getDisplayPath(false);
        $config->postUserStatistics = !empty($this->userId);
        $config->ajaxPath = '/ajax?action=';
        if (config('h5p.saveFrequency') !== false) {
            $config->user = $user;
        }
        $config->canGiveScore = $this->canGiveScore();
        $config->hubIsEnabled = empty($content) && $this->displayH5PHub;

        $ajax = new stdClass();
        $ajax->contentUserData = $this->getContentUserDataUrl();
        $ajax->setFinished = $this->getSetFinishedUrl();
        $config->ajax = $ajax;

        $token = new stdClass();
        $token->result = Uuid::uuid4()->toString();
        $token->contentUserData = Uuid::uuid4()->toString();
        $config->tokens = $token;

        $config->saveFreq = config('h5p.saveFrequency');

        if (is_array($content) && array_key_exists("library", $content)) {
            if (array_key_exists("name", $content["library"])) {
                if (Str::contains($content["library"]["name"], ["H5P.Questionnaire"])) {
                    // The questionnaire does not blindly hammer CA with updates unless there
                    // really is a change.
                    $config->saveFreq = 1;
                }
            }
        }

        $config->siteUrl = $config->baseUrl; // TODO: in production what is this?
        $config->l10n = $this->getl10n();
        $config->loadedJs = [];
        $config->loadedCss = [];
        $config->core = ["scripts" => [], "styles" => []];
        $config->contents = [];
        $config->crossorigin = config('h5p.crossOrigin');
        $config->crossoriginRegex = config('h5p.crossOriginRegexp');
        $config->locale = Session::get('locale', config('app.fallback_locale'));
        $config->localeConverted = LtiToH5PLanguage::convert($config->locale);
        $config->pluginCacheBuster = '?v=' . $this->getCacheBustingVersionString();
        $config->libraryUrl = url('/h5p-php-library/js');

        $this->config = $config;
        return $this->config;
    }

    /**
     * @return array
     */
    protected function getl10n()
    {
        return [
            "H5P" => $this->h5pCore->getLocalization(),
        ];
    }

    /**
     * @return $this
     */
    public function setDisplayHub(bool $displayHub)
    {
        $this->displayH5PHub = $displayHub;
        return $this;
    }

    public function getH5PCore()
    {
        return $this->h5pCore;
    }

    private function getCacheBustingVersionString(): string
    {
        // Previously from H5Plugin::VERSION
        // TODO: figure out if this is important
        return '2.0.3';
    }
}
