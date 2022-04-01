<?php

namespace App\Libraries\H5P\Adapters;

use App\Libraries\H5P\Dataobjects\H5PAlterParametersSettingsDataObject;
use App\Libraries\H5P\Interfaces\H5PAdapterInterface;
use App\Libraries\H5P\Traits\H5PCommonAdapterTrait;
use Cerpus\QuestionBankClient\QuestionBankClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CerpusH5PAdapter implements H5PAdapterInterface
{
    use H5PCommonAdapterTrait;

    public function __construct()
    {
        $this->adapterName = "cerpus";
    }

    /**
     * Alter parameters before added to the H5PIntegrationObject
     *
     * @param string $parameters
     * @param H5PAlterParametersSettingsDataObject|null $settings
     * @return string
     */
    public function alterParameters($parameters, H5PAlterParametersSettingsDataObject $settings = null)
    {
        return QuestionBankClient::convertMathToInlineDisplay($parameters);
    }

    public function getEditorExtraTags($field): array
    {
        return self::getCoreExtraTags();
    }

    /**
     * @return array
     */
    public function getEditorCss(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getEditorSettings(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getCustomEditorScripts(): array
    {
        return ['/js/videos/streamps.js', asset('js/videos/brightcove.js')];
    }

    /**
     * @return array
     */
    public function getCustomViewScripts(): array
    {
        $scripts = [];
        $libraries = $this->config->h5pCore->loadContentDependencies($this->config->id, "preloaded");
        if ($this->hasVideoLibrary($libraries, 1, 3) === true) {
            $scripts[] = '/js/videos/streamps.js';
        }

        if ($this->hasCerpusVideoLibrary($libraries, 1, 0) === true) {
            $scripts[] = '/js/videos/brightcove.js';
        }

        if (config('h5p.include-mathjax') === true) {
            $scripts[] = '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_SVG';
        }
        return $scripts;
    }

    /**
     * @return array
     */
    public function getCustomViewCss(): array
    {
        return [];
    }

    /**
     * @param $semantics
     * @param $machineName
     * @param $majorVersion
     * @param $minorVersion
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
            }
        }
    }

    /**
     * @return void
     */
    public function overrideAdapterSettings()
    {
    }

    /**
     * @return bool
     */
    public function getDefaultImportPrivacy()
    {
        return false; // Shared by default. Corresponds to is_private = false
    }

    public function emptyArticleImportLog($sessionKey = 'message'): void
    {
        DB::table('ndla_article_import_statuses')->truncate();

        session()->flash($sessionKey, 'Article Import Log Emptied.');
    }

    public function resetNdlaIdTracking($sessionKey = 'message'): void
    {
        DB::table('ndla_id_mappers')->truncate();

        session()->flash($sessionKey, 'NDLA ID tracking reset.');
    }

    public function showArticleImportExportFunctionality(): bool
    {
        return true;
    }

    public function runPresaveCommand(): void
    {
        Artisan::call('h5p:addPresave');

        session()->flash('message', 'Presave command run.');
    }

    public function useEmbedLink(): int
    {
        return \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
    }

    public function enableDraftLogic(): bool
    {
        $isEnabled = config("feature.enableDraftLogic");
        return is_null($isEnabled) || filter_var($isEnabled, FILTER_VALIDATE_BOOLEAN);
    }

    public function getExternalProviders(): Collection
    {
        return collect();
    }

    public function useMaxScore(): bool
    {
        return true;
    }

    public function autoTranslateTo(): ?string
    {
        return null;
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
        return [];
    }

    public function getCustomEditorStyles(): array
    {
        return [];
    }
}
