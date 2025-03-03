<?php

namespace App\Libraries\H5P\Packages;

use App\Exceptions\UnknownH5PPackageException;
use App\Libraries\H5P\Helper\H5PPackageProvider;
use LogicException;

class Column extends H5PBase
{
    public static string $machineName = "H5P.Column";
    protected bool $composedComponent = true;
    public static int $majorVersion = 1;
    public static int $minorVersion = 4;

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function getElements(): array
    {
        // TODO: Implement getElements() method.
        throw new LogicException('This method is not implemented');
    }

    public function getAnswers($index = null)
    {
        // TODO: Implement getAnswers() method.
    }

    public function alterSource($sourceFile, array $newSource)
    {
        $contents = collect((array) $this->getContent());
        if ($contents->isEmpty()) {
            return true;
        }

        $contents
            ->pluck('content')
            ->each(function ($content) use ($sourceFile, $newSource) {
                try {
                    $package = H5PPackageProvider::make($content->library, $content->params);
                    if ($package->alterSource($sourceFile, $newSource) !== true) {
                        throw new \Exception("Could not update source");
                    }
                    $content->params = $package->getPackageStructure();
                } catch (UnknownH5PPackageException $exception) {
                }
            });
        return true;
    }

    private function getContent()
    {
        return $this->packageStructure->content;
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }
}
