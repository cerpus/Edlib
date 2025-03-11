<?php

namespace App\Libraries\H5P\Packages;

use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\PackageInterface;

class Questionnaire extends H5PBase
{
    public static string $machineName = "H5P.Questionnaire";
    protected bool $composedComponent = true;

    public function getElements(): array
    {
        $questions = collect((array) $this->packageStructure->questionnaireElements);
        if ($questions->isEmpty()) {
            return [];
        }

        $elements = $questions->map(function ($item, $index) {
            return [
                'item' => H5PPackageProvider::make($item->library->library, $item->library),
                'index' => $index,
            ];
        })->map(function ($items) {
            /** @var PackageInterface $item */
            $item = $items['item'];
            $item->setAnswers($this->getAnswers($items['index']));
            return $item->getElements();
        })->toArray();

        return [
            'elements' => $elements,
            'composedComponent' => $this->isComposedComponent(),
        ];
    }

    public function validate(): bool
    {
        if (
            empty($this->packageStructure) ||
            empty($this->packageStructure->questionnaireElements)
        ) {
            return false;
        }
        return true;
    }

    public function getAnswers($index = null)
    {
        return isset($this->answers->questions[$index]) ? $this->answers->questions[$index] : null;
    }

    public function getPackageAnswers($data)
    {
        return $data->questions;
    }

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function alterConfig(object $config): object
    {
        $config = $this->setUserStateToUnfinished($config);
        $config = $this->setSaveFrequencyToOneSecond($config);

        return $config;
    }


    protected function setUserStateToUnfinished(object $config): object
    {
        $modifiedContentConfigs = (object) [];

        foreach ($config->contents ?? [] as $key => $contentConfigs) {
            $modifiedContentConfigs->{$key} = clone $contentConfigs;
            unset($modifiedContentConfigs->{$key}->contentUserData);
            foreach ($contentConfigs->contentUserData as $contentUserState) {
                $state = json_decode($contentUserState["state"] ?? "{}");
                if (property_exists($state, "finished")) {
                    $state->finished = false;
                }
                $modifiedUserState = json_encode($state);
                $modifiedContentConfigs->{$key}->contentUserData[]["state"] = $modifiedUserState;
            }
        }

        $config->contents = $modifiedContentConfigs;

        return $config;
    }


    protected function setSaveFrequencyToOneSecond(object $config): object
    {
        if ($config->saveFreq !== false) {
            $config->saveFreq = 1;
        }

        return $config;
    }
}
