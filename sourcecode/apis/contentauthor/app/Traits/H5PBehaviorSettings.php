<?php

namespace App\Traits;


use Cerpus\CoreClient\DataObjects\BehaviorSettingsDataObject;
use Cerpus\CoreClient\DataObjects\EditorBehaviorSettingsDataObject;

trait H5PBehaviorSettings
{
    /** @var BehaviorSettingsDataObject */
    protected $behaviorSettings;

    /** @var EditorBehaviorSettingsDataObject */
    protected $editorBehaviorSettings;

    protected $packageStructure;
    private $css = [];

    public function getPackageStructure($asJson = false)
    {
        return $asJson ? json_encode($this->packageStructure) : $this->packageStructure;
    }

    public function applyBehaviorSettings(BehaviorSettingsDataObject $settingsDataObject)
    {
        $this->behaviorSettings = $settingsDataObject;

        $this->handlePresetMode();
        $this->handleRetry();
        $this->handleAutoCheck();
        $this->handleShowSolution();
        $this->handleShowSummary();

        return $this->getPackageStructure(true);
    }

    private function handlePresetMode()
    {
        switch ($this->behaviorSettings->presetmode) {
            case 'exam':
                $this->behaviorSettings->enableRetry = false;
                $this->behaviorSettings->autoCheck = false;
                $this->behaviorSettings->showSolution = true;
                break;
            case 'score':
                $this->behaviorSettings->enableRetry = true;
                $this->behaviorSettings->autoCheck = false;
                $this->behaviorSettings->showSolution = false;
                break;
        }
    }

    public function applyEditorBehaviorSettings(EditorBehaviorSettingsDataObject $settingsDataObject)
    {
        $this->editorBehaviorSettings = $settingsDataObject;
        $this->handleTextAndTranslations();
    }

    protected function handleTextAndTranslations()
    {
        if ($this->editorBehaviorSettings->hideTextAndTranslations === true) {
            $this->addCss(".h5peditor-form .common{display:none;}");
        }
    }

    private function handleRetry()
    {
        if (is_null($this->behaviorSettings->enableRetry)) {
            return;
        }
        $this->alterRetryButton();
        $this->addCss(sprintf('.h5p-flashcards-results.show .h5p-results-retry-button, 
        .h5p-image-sequencing .h5p-image-sequencing-retry 
        { 
            display: %s;
        }', $this->behaviorSettings->enableRetry === true ? 'inline-block' : 'none'));
    }

    private function handleShowSolution()
    {
        if (is_null($this->behaviorSettings->showSolution)) {
            return;
        }

        $this->alterShowSolutionButton();
    }

    private function handleShowSummary()
    {
        if (is_null($this->behaviorSettings->showSummary)) {
            return;
        }

        $this->alterShowSummary();
    }

    protected function alterRetryButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "behaviour" && is_object($values) && property_exists($values, "enableRetry");
            })
            ->transform(function ($values) {
                $values->enableRetry = $this->behaviorSettings->enableRetry;
                return $values;
            })
            ->toArray();
    }

    public function addCss($css)
    {
        $this->css[] = $css;
    }

    public function getCss($asString = false)
    {
        return $asString !== true ? $this->css : implode(PHP_EOL, $this->css);
    }

    private function handleAutoCheck()
    {
        if (is_null($this->behaviorSettings->autoCheck)) {
            return;
        }
        $this->alterAutoCheck();
    }

    protected function alterAutoCheck()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "behaviour" && is_object($values) && property_exists($values, "autoCheck");
            })
            ->transform(function ($values) {
                $values->autoCheck = $this->behaviorSettings->autoCheck;
                return $values;
            })
            ->toArray();
    }

    protected function alterShowSolutionButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "behaviour" && is_object($values) && property_exists($values, "enableSolutionsButton");
            })
            ->transform(function ($values) {
                $values->enableSolutionsButton = $this->behaviorSettings->showSolution;
                return $values;
            })
            ->toArray();
    }

    protected function alterShowSummary()
    {
        //only valid for CoursePresentation at the moment
    }
}
