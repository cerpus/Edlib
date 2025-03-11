<?php

namespace App\Libraries\H5P\Packages;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use App\Libraries\H5P\Interfaces\PackageInterface;

class CoursePresentation extends H5PBase
{
    public static string $machineName = "H5P.CoursePresentation";
    protected bool $composedComponent = true;
    public static int $majorVersion = 1;
    public static int $minorVersion = 16;

    public function getElements(): array
    {
        $interactions = collect((array) $this->getSlides());
        if ($interactions->isEmpty()) {
            return [];
        }

        $elements = $interactions
            ->map(function ($slide) {
                return $slide->elements;
            })
            ->reduce(function ($existingElement, $newElement) {
                return $existingElement->merge($newElement);
            }, collect())
            ->map(function ($item, $index) {
                try {
                    return [
                        'package' => H5PPackageProvider::make($item->action->library, $item->action->params),
                        'index' => $index,
                    ];
                } catch (UnknownH5PPackageException $exception) {
                }
            })
            ->reject(function ($items) {
                if (!isset($items)) {
                    return true;
                }

                /** @var PackageInterface $item */
                $item = $items['package'];
                return is_null($item) || $item->canExtractAnswers() === false || $item->validate() !== true;
            })
            ->map(function ($items) {
                /** @var PackageInterface $item */
                $item = $items['package'];
                $item->setAnswers($this->getAnswers($items['index']));
                return $item->getElements();
            })
            ->toArray();


        return [
            'elements' => $elements,
            'composedComponent' => $this->isComposedComponent(),
        ];
    }

    private function getSlides()
    {
        return $this->packageStructure->presentation->slides;
    }

    public function getAnswers($index = null)
    {
        // TODO: Implement getAnswers() method.
    }

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function validate(): bool
    {
        if (
            empty($this->packageStructure) ||
            empty($this->packageStructure->presentation->slides)
        ) {
            return false;
        }
        return true;
    }

    public function alterSource($sourceFile, array $newSource)
    {
        $slides = collect((array) $this->getSlides());
        if ($slides->isEmpty()) {
            return true;
        }

        $slides
            ->each(function ($slide) use ($sourceFile, $newSource) {
                collect($slide->elements)
                    ->pluck('action')
                    ->each(function ($element, $elementIndex) use ($sourceFile, $newSource, $slide) {
                        if (!is_object($element)) {
                            \Log::error(__METHOD__ . sprintf(" '%s' is of type %s. Source is: %s", $element, gettype($element), $sourceFile));
                            return;
                        }
                        try {
                            $package = H5PPackageProvider::make($element->library, $element->params);
                            if ($package->alterSource($sourceFile, $newSource) !== true) {
                                throw new \Exception("Could not update source");
                            }
                            $element->params = $package->getPackageStructure();
                        } catch (UnknownH5PPackageException $exception) {
                        }
                    });
            });
        return true;
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
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

    protected function alterShowSummary()
    {
        collect($this->packageStructure)
            ->filter(function ($values, $key) {
                return strtolower($key) === "override" && property_exists($values, "hideSummarySlide");
            })
            ->transform(function ($values) {
                $values->hideSummarySlide = !$this->behaviorSettings->showSummary;
                return $values;
            })
            ->toArray();
    }
}
