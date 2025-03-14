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

        $field->font->size = [
            (object) [
                'label' => '50%',
                'css' => '0.5em',
            ],
            (object) [
                'label' => '56.25%',
                'css' => '0.5625em',
            ],
            (object) [
                'label' => '62.50%',
                'css' => '0.625em',
            ],
            (object) [
                'label' => '68.75%',
                'css' => '0.6875em',
            ],
            (object) [
                'label' => '75%',
                'css' => '0.75em',
            ],
            (object) [
                'label' => '87.50%',
                'css' => '0.875em',
            ],
            (object) [
                'label' => '100%',
                'css' => '1em',
            ],
            (object) [
                'label' => '112.50%',
                'css' => '1.125em',
            ],
            (object) [
                'label' => '125%',
                'css' => '1.25em',
            ],
            (object) [
                'label' => '137.50%',
                'css' => '1.375em',
            ],
            (object) [
                'label' => '150%',
                'css' => '1.5em',
            ],
            (object) [
                'label' => '162.50%',
                'css' => '1.625em',
            ],
            (object) [
                'label' => '175%',
                'css' => '1.75em',
            ],
            (object) [
                'label' => '225%',
                'css' => '2.25em',
            ],
            (object) [
                'label' => '300%',
                'css' => '3em',
            ],
            (object) [
                'label' => '450%',
                'css' => '4.5em',
            ],
            (object) [
                'label' => '675%',
                'css' => '6.75em',
            ],
            (object) [
                'label' => '1350%',
                'css' => '13.5em',
            ],
            (object) [
                'label' => '3375%',
                'css' => '33.75em',
            ],
        ];
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
            'wirisPath' => 'https://www.wiris.net/client/plugins/ckeditor/plugin.js',
        ];
    }


    public function getCustomEditorScripts(): array
    {
        $js[] = "/js/h5p/wiris/h5peditor-html-wiris-addon.js";
        $js[] = (string) mix('js/h5peditor-image-popup.js');
        $js[] = (string) mix('js/h5peditor-custom.js');

        return array_unique([
            ...$js,
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
            '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_SVG',
            '/js/h5p/wiris/view.js',
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
            'feature.content-locking',
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

    /**
     * @return bool
     */
    public function getDefaultImportPrivacy()
    {
        return true; // Private by default. Corresponds to is_private = true
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
}
