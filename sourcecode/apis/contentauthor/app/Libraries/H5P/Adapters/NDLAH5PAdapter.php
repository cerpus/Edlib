<?php

namespace App\Libraries\H5P\Adapters;

use App\H5POption;
use App\Libraries\H5P\Audio\NDLAAudioBrowser;
use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\File\NDLATextTrack;
use App\Libraries\H5P\Image\NDLAContentBrowser;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PImageAdapterInterface;
use App\Libraries\H5P\Interfaces\H5PVideoInterface;
use App\Libraries\H5P\Traits\H5PCommonAdapterTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use function Cerpus\Helper\Helpers\profile as config;

class NDLAH5PAdapter implements H5PAdapterInterface
{
    use H5PCommonAdapterTrait;

    /** @var H5PImageAdapterInterface */
    private $imageBrowser;

    /** @var H5PAlterParametersSettingsDataObject */
    private $parameterSettings;

    public function __construct()
    {
        $this->adapterName = "ndla";
    }

    /**
     * Alter parameters before added to the H5PIntegrationObject
     *
     * @param string $parameters
     * @return string
     */
    public function alterParameters($parameters, H5PAlterParametersSettingsDataObject $settings = null)
    {
        $this->imageBrowser = resolve(NDLAContentBrowser::class);
        $this->parameterSettings = $settings ?? resolve(H5PAlterParametersSettingsDataObject::class);
        return $this->traverseParameters(collect(json_decode($parameters)))->toJson();
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

        $field->font->size = array(
            (object)array(
                'label' => '50%',
                'css' => '0.5em'
            ),
            (object)array(
                'label' => '56.25%',
                'css' => '0.5625em'
            ),
            (object)array(
                'label' => '62.50%',
                'css' => '0.625em'
            ),
            (object)array(
                'label' => '68.75%',
                'css' => '0.6875em'
            ),
            (object)array(
                'label' => '75%',
                'css' => '0.75em'
            ),
            (object)array(
                'label' => '87.50%',
                'css' => '0.875em'
            ),
            (object)array(
                'label' => '100%',
                'css' => '1em'
            ),
            (object)array(
                'label' => '112.50%',
                'css' => '1.125em'
            ),
            (object)array(
                'label' => '125%',
                'css' => '1.25em'
            ),
            (object)array(
                'label' => '137.50%',
                'css' => '1.375em'
            ),
            (object)array(
                'label' => '150%',
                'css' => '1.5em'
            ),
            (object)array(
                'label' => '162.50%',
                'css' => '1.625em'
            ),
            (object)array(
                'label' => '175%',
                'css' => '1.75em'
            ),
            (object)array(
                'label' => '225%',
                'css' => '2.25em'
            ),
            (object)array(
                'label' => '300%',
                'css' => '3em'
            ),
            (object)array(
                'label' => '450%',
                'css' => '4.5em'
            ),
            (object)array(
                'label' => '675%',
                'css' => '6.75em'
            ),
            (object)array(
                'label' => '1350%',
                'css' => '13.5em'
            ),
            (object)array(
                'label' => '3375%',
                'css' => '33.75em'
            )
        );
    }

    /**
     * @return array
     */
    public function getEditorCss(): array
    {
        $css = [(string) mix('css/ndlah5p-editor.css')];
        $css[] = '/js/cropperjs/cropper.min.css';
        if (config('h5p.include-custom-css') === true) {
            $css[] = (string) mix('css/ndlah5p-edit.css');
        }
        return $css;
    }

    /**
     * @return array
     */
    public function getEditorSettings(): array
    {
        return [
            'wirisPath' => 'https://www.wiris.net/client/plugins/ckeditor/plugin.js',
        ];
    }

    /**
     * @return array
     */
    public function getCustomEditorScripts(): array
    {
        return [
            "/js/h5p/wiris/h5peditor-html-wiris-addon.js",
            (string) mix("js/ndla-contentbrowser.js"),
            "/js/videos/brightcove.js",
            (string) mix('js/h5peditor-image-popup.js'),
            (string) mix('js/h5peditor-custom.js'),
        ];
    }

    public function getCustomEditorStyles(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCustomViewScripts(): array
    {
        $scripts = [
            '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_SVG',
            '/js/h5p/wiris/view.js',
            (string) mix('js/h5peditor-custom.js'),
        ];
        $libraries = $this->config->h5pCore->loadContentDependencies($this->config->id, "preloaded");
        if ($this->hasVideoLibrary($libraries, 1, 3) === true) {
            $scripts[] = '/js/videos/brightcove.js';
        }
        return $scripts;
    }

    /**
     * @return array
     */
    public function getCustomViewCss(): array
    {
        $css = [];
        $ndlaCustomCssOption = H5POption::where('option_name', H5POption::NDLA_CUSTOM_CSS_TIMESTAMP)->first();
        if ($ndlaCustomCssOption && !empty($this->config->content)) {
            $customCssBreakpoint = Carbon::parse($ndlaCustomCssOption->option_value);
            $updated = $this->config->content['updated_at'];
            if ($customCssBreakpoint > $updated) {
                $css[] = (string) mix('css/ndlah5p-iframe-legacy.css');
            }
        }
        $css[] = (string) mix('css/ndlah5p-iframe.css');
        return $css;
    }

    /**
     * @param $semantics
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
                    $field->tags = array();
                }
                $field->tags = array_merge($field->tags, $this->getEditorExtraTags($field));
            } elseif (in_array($field->type, ['image', 'video', 'file'])) {
                if (!isset($field->extraAttributes)) {
                    $field->extraAttributes = array('externalId');
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
        config(['app.deploymentEnvironment' => 'ndlaprod']);
        config(collect([
            'app.enable_licensing',
            'feature.versioning',
            'feature.licensing',
            'feature.content-locking',
            'feature.context-collaboration',
            'feature.collaboration',
            'feature.export_h5p_on_save',
            'export_h5p_with_local_files',
            'ndla.userId',
            'h5p.image.authDomain',
            'h5p.image.key',
            'h5p.image.secret',
            'h5p.image.audience',
            'h5p.image.url',
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
            'feature.enableDraftLogic',
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

    public function emptyArticleImportLog($sessionKey = 'message'): void
    {
        session()->flash($sessionKey, 'Article Import Log NOT Emptied.');
    }

    public function resetNdlaIdTracking($sessionKey = 'message'): void
    {
        session()->flash($sessionKey, 'NDLA ID tracking NOT Reset.');
    }

    public function showArticleImportExportFunctionality(): bool
    {
        return false;
    }

    public function runPresaveCommand(): void
    {
        session()->flash('message', 'Presave command NOT run.');
    }

    public function useEmbedLink(): int
    {
        return \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
    }

    public function enableDraftLogic(): bool
    {
        return filter_var(config("feature.enableDraftLogic"), FILTER_VALIDATE_BOOLEAN);
    }

    public function showNorgesfilmAdmin(): bool
    {
        return false;
    }

    public function getExternalProviders(): Collection
    {
        return collect([
            resolve(NDLAContentBrowser::class),
            resolve(H5PVideoInterface::class),
            resolve(NDLAAudioBrowser::class),
            resolve(NDLATextTrack::class),
        ]);
    }

    public function useMaxScore(): bool
    {
        return false;
    }

    public function autoTranslateTo(): ?string
    {
        return 'nno';
    }

    private function traverseParameters(Collection $values): Collection
    {
        return $values->map(function ($value) {
            if ($this->isImageTarget($value)) {
                $value = $this->imageBrowser->alterImageProperties($value, $this->parameterSettings->useImageWidth);
            }
            if (!!(array)$value && (is_array($value) || is_object($value))) {
                return $this->traverseParameters(collect($value));
            }

            return $value;
        });
    }

    private function isImageTarget($value): bool
    {
        return is_object($value) && !empty($value->mime) && !empty($value->path) && $this->imageBrowser->isTargetType($value->mime, $value->path);
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
        return [
            (string) mix('js/react-contentbrowser.js')
        ];
    }
}
