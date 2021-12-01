<?php

namespace App\Libraries\H5P;

use App\H5PLibrary;
use App\Libraries\H5P\Interfaces\ConfigInterface;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Traits\H5PBehaviorSettings;
use H5peditor;
use Illuminate\Support\Facades\Session;
use function Cerpus\Helper\Helpers\profile as config;


class EditorConfig implements ConfigInterface
{
    use Config, H5PBehaviorSettings;

    public $id;
    private $editor;
    private $redirectToken;
    private $contentValidator;
    private $hideH5pJS = false;
    private $language;


    public function __construct(H5PAdapterInterface $adapter, \H5PCore $core, H5peditor $editor, \H5PContentValidator $validator)
    {
        $this->preview = true;
        $this->adapter = $adapter;
        $this->fileStorage = $core->fs;
        $this->h5pCore = $core;
        $this->editor = $editor;
        $this->contentValidator = $validator;
    }

    /**
     * @param null $id
     * @return EditorConfig
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    private function init()
    {
        $this->initConfig();
        $this->initEditorConfig();
        $this->addCoreAssets();
        $this->addDefaultEditorAssets();
        $this->addCustomEditorStyles();
    }

    public function hideH5pJS() {
        $this->hideH5pJS = true;
        return $this;
    }

    public function getConfig()
    {
        $this->init();
        return $this->config;

    }

    public function setRedirectToken($token)
    {
        $this->redirectToken = $token;
        return $this;
    }

    public function setLanguage(?string $languageCode)
    {
        $this->language = $languageCode;
        return $this;
    }

    private function initEditorConfig()
    {
        $editorConfig = new \stdClass();
        $editorConfig->assets = $this->getEditorAssets();
        $editorConfig->libraryUrl = "/h5p-editor-php-library/";
        $editorConfig->copyrightSemantics = $this->contentValidator->getCopyrightSemantics();
        $editorConfig->metadataSemantics = $this->contentValidator->getMetadataSemantics();
        $content = $this->getContent();
        $editorConfig->ajaxPath = sprintf("/ajax?redirectToken=%s&h5p_id=%s&action=", $this->redirectToken, $content['id'] ?? '');
        if (!empty($content['id'])) {
            $editorConfig->nodeVersionId = $content['id'];
            $this->setLibrary(H5PLibrary::find($content['library']['id']));
        }
        $editorConfig->filesPath = $this->fileStorage->getEditorDisplayPath(false);
        $editorConfig->fileIcon = array(
            'path' => '/h5p-editor-php-library/images/binary-file.png',
            'width' => 50,
            'height' => 50,
        );
        $editorConfig->apiVersion = \H5PCore::$coreApi;
        $editorSettings = $this->adapter->getEditorSettings();
        if (!empty($editorSettings)) {
            $editorConfig = (object)array_merge((array)$editorConfig, $editorSettings);
        }
        $editorConfig->extraAllowedContent = implode(" ", $this->adapter::getCoreExtraTags());
        $editorConfig->language = $this->language ?? $content['language'] ?? 'en';
        $defaultLanguage = config("h5p.default-resource-language");
        $editorConfig->defaultLanguage = \Iso639p3::code2letters($defaultLanguage);

        $this->config->editor = $editorConfig;
    }

    private function getEditorAssets()
    {
        $assets = new \stdClass();
        $assets->css = $this->getEditorStyles();
        $assets->js = array_merge($this->getEditorScripts(), $this->addCustomScripts());
        return $assets;
    }

    private function getEditorStyles()
    {
        $editor = $this->editor;
        $editorStyles[] = (string) mix('css/h5p-core.css');
        $editorStyles[] = (string) mix('css/h5p-admin.css');
        foreach ($editor::$styles as $style) {
            $editorStyles[] = $this->getAssetUrl("editor", $style);
        }
        $editorStyles[] = '//fonts.googleapis.com/css?family=Lato:400,700';
        return array_merge($editorStyles, $this->adapter->getEditorCss());
    }

    private function getEditorScripts()
    {
        $editorScripts = [];

        $core = $this->h5pCore;
        foreach ($core::$scripts as $script) {
            $scriptPath = $this->getAssetUrl("core", $script);
            $editorScripts[] = $scriptPath;
        }

        $replaceScripts = [
            'scripts/h5peditor-metadata-author-widget.js',
            'scripts/h5peditor-metadata.js',
            'scripts/h5peditor-number.js',
            'scripts/h5peditor-select.js',
            'scripts/h5peditor-text.js',
            'scripts/h5peditor-textarea.js',
            'scripts/h5peditor-editor.js',
            'scripts/h5peditor-list-editor.js',
            'scripts/h5peditor-list.js',
        ];
        $editor = $this->editor;
        foreach ($editor::$scripts as $script) {
            if (!in_array($script, $replaceScripts)) {
                $editorScripts[] = $this->getAssetUrl("editor", $script);
            }
        }

        foreach ([
                     (string) mix("js/h5pmetadata.js"),
                     '/js/editor-setup.js',
                 ] as $script) {
            $scriptPath = $this->getAssetUrl(null, $script);
            $editorScripts[] = $scriptPath;
        }

        return $editorScripts;
    }

    /**
     * @return array
     */
    private function addCustomScripts()
    {
        $customScripts = array_map(function ($script){
            return $this->getAssetUrl(null, $script);
        }, $this->adapter->getCustomEditorScripts());
        return $customScripts;
    }

    private function addDefaultEditorAssets()
    {
        $this->addAsset("scripts", $this->getAssetUrl(null, "/js/cerpus.js"));
        $this->addAsset("scripts", $this->getAssetUrl("editor", "scripts/h5peditor-editor.js"));
        $this->addAsset("scripts", $this->getAssetUrl("editor", "scripts/h5peditor-init.js"));
        if (!$this->hideH5pJS) {
            $this->addAsset("scripts", $this->getAssetUrl(null, "js/h5p-editor.js"));
        }
        $this->addAsset("scripts", $this->getAssetUrl("editor", $this->getLanguage()));
    }

    private function getLanguage()
    {
        $preferredH5PLanguage = LtiToH5PLanguage::convert(Session::get('locale'));
        if ($this->languageFileExists($preferredH5PLanguage)) {
            return "language/$preferredH5PLanguage.js";
        }

        return 'language/en.js';
    }

    private function languageFileExists($preferredLanguage)
    {
        $path = public_path('h5p-editor-php-library/language/' . $preferredLanguage . '.js');

        $exists = file_exists($path);
        return $exists;
    }

    public function addCustomEditorStyles()
    {
        foreach ($this->adapter->getCustomEditorStyles() as $style){
            $this->addAsset('styles', $style);
        }
    }
}
