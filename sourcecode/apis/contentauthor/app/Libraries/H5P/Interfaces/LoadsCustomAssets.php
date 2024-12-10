<?php

declare(strict_types=1);

namespace App\Libraries\H5P\Interfaces;

interface LoadsCustomAssets
{
    /**
     * @return string[]
     */
    public function getViewCss(): array;

    /**
     * @return string[]
     */
    public function getViewScripts(): array;

    /**
     * @return string[]
     */
    public function getEditorCss(): array;

    /**
     * @return string[]
     */
    public function getEditorScripts(): array;

    /**
     * @return string[]
     */
    public function getConfigJs(): array;
}
