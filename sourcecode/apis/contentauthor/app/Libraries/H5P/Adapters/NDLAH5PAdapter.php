<?php

namespace App\Libraries\H5P\Adapters;

use App\H5POption;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PAudioInterface;
use App\Libraries\H5P\Interfaces\H5PImageInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Traits\H5PCommonAdapterTrait;
use Carbon\Carbon;

use function array_unique;
use function config;

use const JSON_THROW_ON_ERROR;

class NDLAH5PAdapter implements H5PAdapterInterface
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

        return $this->traverseParameters(collect(json_decode($parameters, flags: JSON_THROW_ON_ERROR)), $settings)->toJson();
    }

    public function getEditorExtraTags($field): array
    {
        $this->addAdditionalFontSizes($field);

        return self::getCoreExtraTags();
    }

    private function addAdditionalFontSizes($field)
    {
        // Add extra font-size options to all CKEditor fields
        if (empty($field->font)) {
            $field->font = new \stdClass();
        }

        $field->font->size = collect([
            '50%', '56.25%', '62.50%', '68.75%', '75%', '87.50%', '100%', '112.50%', '125%', '137.50%',
            '150%', '162.50%', '175%', '225%', '300%', '450%', '675%', '1350%', '3375%',
        ])
            ->map(fn(string $size) => (object) [
                'label' => $size,
                'css' => $size,
            ])
            ->toArray();
    }


    public function getEditorCss(): array
    {
        $css[] = '/js/cropperjs/cropper.min.css';
        if (config('h5p.include-custom-css') === true) {
            $css[] = (string) mix('css/ndlah5p-edit.css');
        }
        return array_unique([
            ...$this->audioAdapter->getEditorCss(),
            ...$this->imageAdapter->getEditorCss(),
            ...$this->videoAdapter->getEditorCss(),
            ...$css,
        ]);
    }


    public function getEditorSettings(): array
    {
        return [
            'wysiwygButtons' => [
                'language',
                'mathtype',
            ],
            'textPartLanguages' =>
                collect(explode(',', config('h5p.ckeditor.textPartLanguages', '')))
                    ->map(fn(string $language) => [
                        'title' => locale_get_display_name($language, app()->getLocale()),
                        'languageCode' => $language,
                    ])
                    ->sortBy('title')
                    ->values(),
        ];
    }


    public function getCustomEditorScripts(): array
    {
        return array_unique([
            // Custom HTML component to enable CKEDitor 5 plugins TextPartLanguage, MathType and ChemType
            (string) mix('js/ndla-h5peditor-html.js'),
            // Custom image editor/cropper
            (string) mix('js/h5peditor-image-popup.js'),
            // H5P.getCrossOrigin override
            (string) mix('js/h5peditor-custom.js'),
            ...$this->audioAdapter->getEditorScripts(),
            ...$this->imageAdapter->getEditorScripts(),
            ...$this->videoAdapter->getEditorScripts(),
        ]);
    }

    public function getCustomEditorStyles(): array
    {
        return $this->videoAdapter->getEditorCss();
    }

    public function getCustomViewScripts(): array
    {
        return [
            // Display of formulas
            '//www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image',
            (string) mix('js/h5peditor-custom.js'),
            ...$this->audioAdapter->getViewScripts(),
            ...$this->imageAdapter->getViewScripts(),
            ...$this->videoAdapter->getViewScripts(),
        ];
    }

    public function getCustomViewCss(): array
    {
        $css = [];
        $ndlaCustomCssOption = H5POption::where('option_name', H5POption::NDLA_CUSTOM_CSS_TIMESTAMP)->first();
        $content = $this->config->getContent();
        if ($ndlaCustomCssOption && !empty($content)) {
            $customCssBreakpoint = Carbon::parse($ndlaCustomCssOption->option_value);
            $updated = $content['updated_at'];
            if ($customCssBreakpoint > $updated) {
                $css[] = (string) mix('css/ndlah5p-iframe-legacy.css');
            }
        }
        $css[] = (string) mix('css/ndlah5p-iframe.css');
        return array_unique([
            ...$css,
            ...$this->audioAdapter->getViewCss(),
            ...$this->imageAdapter->getViewCss(),
            ...$this->videoAdapter->getViewCss(),
        ]);
    }

    /**
     * @return void
     */
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
            } elseif (in_array($field->type, ['image', 'video', 'file'])) {
                if (!isset($field->extraAttributes)) {
                    $field->extraAttributes = ['externalId'];
                } elseif (!in_array('externalId', $field->extraAttributes)) {
                    $field->extraAttributes[] = 'externalId';
                }
            }
        }
    }

    /**
     * @return void
     */
    public function overrideAdapterSettings()
    {
        config(collect([
            'app.enable_licensing',
            'feature.licensing',
            'feature.context-collaboration',
            'feature.collaboration',
            'feature.export_h5p_on_save',
            'export_h5p_with_local_files',
            'h5p.video.enable',
            'h5p.video.deleteVideoSourceAfterConvertToStream',
            'h5p.video.pingDelay',
            'h5p.H5P_DragQuestion',
            'h5p.H5P_Dialogcards',
            'h5p.isHubEnabled',
            'h5p.displayPropertiesBox',
            'h5p.crossOrigin',
            'h5p.crossOriginRegexp',
            'h5p.overrideDisableSetting',
            'h5p.saveFrequency',
            'h5p.defaultExportOption',
            'h5p.defaultShareSetting',
            'h5p.showDisplayOptions',
        ])
            ->mapWithKeys(function ($configKey) {
                return [$configKey => config('ndla-mode.' . $configKey, config($configKey))];
            })
            ->toArray());
    }

    public function useEmbedLink(): int
    {
        return \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
    }

    public function useMaxScore(): bool
    {
        return false;
    }

    public function addTrackingScripts(): ?string
    {
        return null;
        //        return <<<TRACKINGDOC
        //<!-- Global site tag (gtag.js) - Google Analytics -->
        //<script async src="https://www.googletagmanager.com/gtag/js?id=G-R51SSMVE78"></script> <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);}gtag('js', new Date()); gtag('config', 'G-R51SSMVE78'); </script>
        //TRACKINGDOC;
    }

    public function enableEverybodyIsCollaborators(): bool
    {
        return true;
    }

    public function getConfigJs(): array
    {
        return array_unique([
            ...$this->audioAdapter->getConfigJs(),
            ...$this->imageAdapter->getConfigJs(),
            ...$this->videoAdapter->getConfigJs(),
        ]);
    }

    public function getAdapterName(): string
    {
        return 'ndla';
    }

    public function filterEditorScripts(): array
    {
        return [
            // Remove default HTML component. Custom version added in getCustomEditorScripts()
            'ckeditor/ckeditor.js',
            'scripts/h5peditor-html.js',
        ];
    }
}
