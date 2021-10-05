<?php

namespace App\Libraries\H5P\Packages;

class ImagePair extends H5PBase
{
    public static $machineName = "H5P.ImagePair";
    protected $composedComponent = false;
    protected $majorVersion = 1;
    protected $minorVersion = 4;

    /**
     * Only the retry functionality seems to be enabled/used in ImagePair and is controlled
     * by setting the behaviour property to true|false, not an object as you normally
     * would do, and the package semantics claims.
     */
    public function alterRetryButton()
    {
        if (is_object($this->packageStructure) && property_exists($this->packageStructure, 'behaviour')) {
            $this->packageStructure->behaviour = (bool)$this->behaviorSettings->enableRetry;
        }
    }

    /**************************************************************************************
     *  Implement if needed
     **************************************************************************************/

    public function populateSemanticsFromData($data)
    {
        // TODO: Implement populateSemanticsFromData() method.
    }

    public function getElements(): array
    {
        // TODO: Implement getElements() method.
    }

    public function getAnswers($index = null)
    {
        // TODO: Implement getAnswers() method.
    }

    public function getPackageAnswers($data)
    {
        // TODO: Implement getPackageAnswers() method.
    }

    public function getPackageSemantics()
    {
        // TODO: Implement getPackageSemantics() method.
    }
}
