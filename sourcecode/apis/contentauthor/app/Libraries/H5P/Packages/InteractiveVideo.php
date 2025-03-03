<?php

namespace App\Libraries\H5P\Packages;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\PackageInterface;

class InteractiveVideo extends H5PBase
{
    public static string $machineName = "H5P.InteractiveVideo";
    protected bool $composedComponent = true;

    public function getElements(): array
    {
        $interactions = collect((array) $this->getInteractions());
        if ($interactions->isEmpty()) {
            return [];
        }

        $elements = $interactions
            ->map(function ($item, $index) {
                try {
                    return [
                        'package' => H5PPackageProvider::make($item->action->library, $item->action->params),
                        'index' => $index,
                    ];
                } catch (UnknownH5PPackageException $exception) {
                }
            })->reject(function ($items) {
                /** @var PackageInterface|null $item */
                $item = $items['package'] ?? null;
                return is_null($item) || $item->canExtractAnswers() === false || $item->validate() !== true;
            })->map(function ($items) {
                /** @var PackageInterface $item */
                $item = $items['package'];
                $item->setAnswers($this->getAnswers($items['index']));
                return $item->getElements();
            })->toArray();


        return [
            'elements' => $elements,
            'composedComponent' => $this->isComposedComponent(),
        ];
    }

    private function getInteractions()
    {
        return $this->packageStructure->interactiveVideo->assets->interactions;
    }

    public function getAnswers($index = null)
    {
        return isset($this->answers->answers[$index]) ? $this->answers->answers[$index] : null;
    }

    public function validate(): bool
    {
        if (
            empty($this->packageStructure) ||
            empty($this->packageStructure->interactiveVideo->assets->interactions)
        ) {
            return false;
        }
        return true;
    }

    public function getPackageAnswers($data)
    {
        return $data->answers;
    }

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function getSources()
    {
        return $this->packageStructure->interactiveVideo->video->files ?? [];
    }

    public function alterSource($sourceFile, array $newSource)
    {
        $files = $this->getSources();
        if (empty($files) && !empty($sourceFile) && !empty($newSource) && count($newSource) === 2) {
            $files = [(object) ["path" => $sourceFile . "#tmp"]];
        }

        if (empty($files)) {
            return false;
        }

        foreach ($files as $index => $file) {
            if ($file->path === $sourceFile . "#tmp") {
                list($source, $mimetype) = $newSource;
                $files[$index]->path = $source;
                $files[$index]->mime = $mimetype;
            }
        }
        $this->packageStructure->interactiveVideo->video->files = $files;
        return true;
    }

    protected function alterRetryButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "override";
            })
            ->transform(function ($values) {
                if ($this->behaviorSettings->enableRetry === true) {
                    $values->retryButton = 'on';
                } elseif ($this->behaviorSettings->enableRetry === false) {
                    $values->retryButton = 'off';
                }
                return $values;
            })
            ->toArray();
    }

    protected function alterShowSolutionButton()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "override";
            })
            ->transform(function ($values) {
                if ($this->behaviorSettings->showSolution === true) {
                    $values->showSolutionButton = 'on';
                } elseif ($this->behaviorSettings->showSolution === false) {
                    $values->showSolutionButton = 'off';
                }
                return $values;
            })
            ->toArray();
    }
}
