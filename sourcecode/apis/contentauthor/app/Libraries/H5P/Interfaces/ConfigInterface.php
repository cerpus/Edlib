<?php

namespace App\Libraries\H5P\Interfaces;


use App\H5PLibrary;

interface ConfigInterface
{
    public function getConfig();

    public function getScriptAssets();

    public function getStyleAssets();

    public function setH5pPlugin($plugin);

    public function setContent($content);

    public function setLibrary(H5PLibrary $library);

    public function getH5PCore();
}