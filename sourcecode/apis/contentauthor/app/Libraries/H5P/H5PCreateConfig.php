<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use Iso639p3;

class H5PCreateConfig extends H5PConfigAbstract
{
    public function __construct(H5PAdapterInterface $adapter, \H5PCore $h5pCore)
    {
        parent::__construct($adapter, $h5pCore);

        $validator = app(\H5PContentValidator::class);
        $this->editorConfig = [
            'assets' => $this->getEditorAssets(),
            'libraryUrl' => '/h5p-editor-php-library/'  ,
            'copyrightSemantics' => $validator->getCopyrightSemantics(),
            'metadataSemantics' => $validator->getMetadataSemantics(),
            'ajaxPath' => '',
            'filesPath' => $this->h5pCore->fs->getEditorDisplayPath(false),
            'fileIcon' => [
                'path' => '/h5p-editor-php-library/images/binary-file.png',
                'width' => 50,
                'height' => 50,
            ],
            'apiVersion' => \H5PCore::$coreApi,
            'extraAllowedContent' => implode(" ", $this->adapter::getCoreExtraTags()),
            'language' => '',
            'defaultLanguage' => Iso639p3::code2letters(config("h5p.default-resource-language")),
        ];
        $this->config['ajax']['contentUserData'] = '/api/progress?action=h5p_preview&c=1';
        $this->config['ajax']['setFinished'] = '/api/progress?action=h5p_preview&f=1';

        $editorSettings = $this->adapter->getEditorSettings();
        if (!empty($editorSettings)) {
            $this->editorConfig = array_merge($this->editorConfig, $editorSettings);
        }

        $this->addCoreAssets();
        $this->addDefaultEditorAssets();
        $this->addCustomEditorStyles();
    }

    public function setDisplayHub(bool $display): static
    {
        $this->config['hubIsEnabled'] = $display;
        return $this;
    }

    protected function addInheritorConfig(): void
    {
        $this->editorConfig['language'] = $this->language ?? Iso639p3::code2letters(config("h5p.default-resource-language"));
        $this->editorConfig['ajaxPath'] = sprintf("/ajax?redirectToken=%s&h5p_id=&action=", $this->redirectToken);

        $this->config['editor'] = (object) $this->editorConfig;

        $imageBrowser = app(H5PImageAdapterInterface::class);
        if ($imageBrowser) {
            $this->config['imageBrowserDetailsUrl'] = $imageBrowser::getClientDetailsUrl();
        }
        $audioBrowser = app(H5PAudioInterface::class);
        if ($audioBrowser) {
            $this->config['audioBrowserDetailsUrl'] = $audioBrowser::getClientDetailsUrl();
        }
    }
}
