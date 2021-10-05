<?php

namespace App\Libraries\H5P;

use DB;

class H5Plugin
{

    const VERSION = '2.0.2';

    protected static $instance;
    protected static $interface;
    protected static $core;
    protected static $settings;

    private $language;
    private $uploadPath;
    private $basePath;
    private $publicRoot;
    private $pdo;

    private function __construct($pdo = null)
    {
        $this->basePath = dirname(dirname(dirname(__DIR__)));
        $this->pdo = $pdo;
        if (is_null($this->pdo)) {
            $this->pdo = DB::connection()->getPdo();
        }
        $this->publicRoot = env('TEST_FS_ROOT', $_SERVER['DOCUMENT_ROOT']);
    }

    public static function setUp()
    {
        self::$core = null;
        self::$instance = null;
        self::$interface = null;
        self::$settings = null;
    }

    /**
     * @param \PDO $pdo
     * @return H5Plugin
     */
    public static function get_instance(\PDO $pdo = null)
    {
        if (is_null($pdo)) {
            $pdo = DB::connection()->getPdo();
        }
        if (null == self::$instance) {
            self::$instance = new self($pdo);
        }

        return self::$instance;
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function getPublicRoot()
    {
        return $this->publicRoot;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language, $defaultLanguage = "en")
    {
        if (is_null($language)) {
            $language = $defaultLanguage;
        }
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    public function getPath($relative = true)
    {
        return ($relative ? $this->publicRoot : "") . "/h5pstorage";
    }

    /**
     * @param string $uploadPath
     */
    public function setUploadPath($uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }

}