<?php

declare(strict_types=1);

namespace App\Libraries\H5P;

use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Traits\H5PBehaviorSettings;

class H5PEditConfig extends H5PConfigAbstract
{
    use H5PBehaviorSettings;

    public function __construct(H5PAdapterInterface $adapter, \H5PCore $h5pCore)
    {
        parent::__construct($adapter, $h5pCore);

        $validator = app(\H5PContentValidator::class);
        $this->editorConfig = [
            'assets' => $this->getEditorAssets(),
            'libraryUrl' => '/h5p-editor-php-library/',
            'copyrightSemantics' => $validator->getCopyrightSemantics(),
            'metadataSemantics' => $validator->getMetadataSemantics(),
            'ajaxPath' => '',
            'nodeVersionId' => null,
            'filesPath' => $this->h5pCore->fs->getEditorDisplayPath(false),
            'fileIcon' => [
                'path' => '/h5p-editor-php-library/images/binary-file.png',
                'width' => 50,
                'height' => 50,
            ],
            'apiVersion' => \H5PCore::$coreApi,
            'extraAllowedContent' => implode(" ", $this->adapter::getCoreExtraTags()),
            'language' => '',
            'defaultLanguage' => \Iso639p3::code2letters(config("h5p.default-resource-language")),
        ];
        $this->config['ajax']['contentUserData'] = '/api/progress?action=h5p_preview&c=1';
        $this->config['ajax']['setFinished'] = '/api/progress?action=h5p_preview&f=1';

        $this->addCoreAssets();
        $this->addDefaultEditorAssets();
        $this->addCustomEditorStyles();
    }

    public function loadContent(int $id): static
    {
        parent::loadContent($id);

        $this->editorConfig['nodeVersionId'] = $this->content['id'];
        $this->config['canGiveScore'] = !($this->content['max_score'] === null) && $this->content['max_score'] > 0;

        return $this;
    }

    protected function addInheritorConfig(): void
    {
        $this->editorConfig['language'] = $this->language ?? $this->content['language'] ?? 'en';
        if ($this->content) {
            $this->editorConfig['ajaxPath'] = sprintf("/ajax?redirectToken=%s&h5p_id=%s&action=", $this->redirectToken, $this->content['id']);
        }
        $this->config['editor'] = (object) $this->editorConfig;
    }
}
