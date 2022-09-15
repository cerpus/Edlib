<?php

namespace App\Libraries\Games\Millionaire;

class AppManifest
{
    private $manifest;
    public static $NAME = 'CERPUS.MILLIONAIRE';

    /**
     * AppManifest constructor.
     * @param $manifest
     */
    public function __construct($manifest)
    {
        $this->manifest = $manifest;
    }

    public function getTitle()
    {
        return ucfirst($this->manifest->name);
    }

    public function getName()
    {
        return self::$NAME;
    }

    public function getVersion()
    {
        return $this->getMajorVersion() . '.' . $this->getMinorVersion();
    }

    public function getMajorVersion()
    {
        return explode('.', $this->manifest->description)[0];
    }

    public function getMinorVersion()
    {
        return explode('.', $this->manifest->description)[1];
    }
}
