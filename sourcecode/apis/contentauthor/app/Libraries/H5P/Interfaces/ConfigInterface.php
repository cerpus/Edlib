<?php

namespace App\Libraries\H5P\Interfaces;

use App\H5PLibrary;

/**
 * @property mixed $id
 */
interface ConfigInterface
{
    public function getConfig();

    public function getScriptAssets();

    public function getStyleAssets();

    public function getH5PCore();
}
