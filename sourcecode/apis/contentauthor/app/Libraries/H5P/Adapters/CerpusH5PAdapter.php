<?php

namespace App\Libraries\H5P\Adapters;

use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Traits\H5PCommonAdapterTrait;
use Cerpus\QuestionBankClient\QuestionBankClient;

use function array_unique;
use function json_decode;

use const JSON_THROW_ON_ERROR;

class CerpusH5PAdapter implements H5PAdapterInterface
{
    use H5PCommonAdapterTrait;

    public function __construct(
        private readonly H5PAudioInterface $audioAdapter,
        private readonly H5PImageInterface $imageAdapter,
        private readonly H5PVideoInterface $videoAdapter,
    ) {}

    public function alterParameters(
        string $parameters,
        H5PAlterParametersSettingsDataObject $settings = new H5PAlterParametersSettingsDataObject(),
    ): string {
        if ($parameters === '') {
            return '';
        }

        $parameters = QuestionBankClient::convertMathToInlineDisplay($parameters);

        return $this->traverseParameters(collect(json_decode($parameters, flags: JSON_THROW_ON_ERROR)), $settings)->toJson();
    }

    public function getEditorExtraTags($field): array
    {
        return self::getCoreExtraTags();
    }

    public function getEditorCss(): array
    {
        return array_unique([
            ...$this->audioAdapter->getEditorCss(),
            ...$this->imageAdapter->getEditorCss(),
            ...$this->videoAdapter->getEditorCss(),
        ]);
    }

    public function getEditorSettings(): array
    {
        return [];
    }

    public function getCustomEditorScripts(): array
    {
        return array_unique([
            ...$this->audioAdapter->getEditorScripts(),
            ...$this->imageAdapter->getEditorScripts(),
            ...$this->videoAdapter->getEditorScripts(),
        ]);
    }

    public function getCustomViewScripts(): array
    {
        $scripts = [];

        if (config('h5p.include-mathjax') === true) {
            $scripts[] = '//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-svg.js';
        }

        return array_unique([
            ...$scripts,
            ...$this->audioAdapter->getViewScripts(),
            ...$this->imageAdapter->getViewScripts(),
            ...$this->videoAdapter->getViewScripts(),
        ]);
    }

    public function getCustomViewCss(): array
    {
        return array_unique([
            ...$this->audioAdapter->getViewCss(),
            ...$this->imageAdapter->getViewCss(),
            ...$this->videoAdapter->getViewCss(),
        ]);
    }

    public function alterLibrarySemantics(&$semantics, $machineName, $majorVersion, $minorVersion)
    {
        $this->alterPackageSemantics($semantics, $machineName);
        foreach ($semantics as $field) {
            // Lists specify the field inside the list.
            while ($field->type == 'list') {
                $field = $field->field;
            }

            if ($field->type == 'group') {
                // Recurse for group.
                $this->alterLibrarySemantics($field->fields, null, null, null);
            } elseif ($field->type == 'text' && isset($field->widget) && $field->widget == 'html') {
                // Add MathML tags necessary for the NDLA MathML extension to HTML text widget.
                if (!isset($field->tags)) {
                    $field->tags = [];
                }
                $field->tags = array_merge($field->tags, $this->getEditorExtraTags($field));
            }
        }
    }

    /**
     * @return void
     */
    public function overrideAdapterSettings() {}

    /**
     * @return bool
     */
    public function getDefaultImportPrivacy()
    {
        return false; // Shared by default. Corresponds to is_private = false
    }

    public function useEmbedLink(): int
    {
        return \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
    }

    public function useMaxScore(): bool
    {
        return true;
    }

    public function addTrackingScripts(): ?string
    {
        return null;
    }

    public function enableEverybodyIsCollaborators(): bool
    {
        return false;
    }

    public function getConfigJs(): array
    {
        return array_unique([
            ...$this->audioAdapter->getConfigJs(),
            ...$this->imageAdapter->getConfigJs(),
            ...$this->videoAdapter->getConfigJs(),
        ]);
    }

    public function getCustomEditorStyles(): array
    {
        return array_unique([
            ...$this->audioAdapter->getEditorCss(),
            ...$this->imageAdapter->getEditorCss(),
            ...$this->videoAdapter->getEditorCss(),
        ]);
    }

    public function getAdapterName(): string
    {
        return 'cerpus';
    }

    public function filterEditorScripts(): array
    {
        return [];
    }
}
