<?php

namespace App\Libraries\Storage;

use Illuminate\Support\Facades\Storage;

class LogStorage extends Storage
{
    public const STORAGE = "storageLogs";
    public static $disk;

    public static function init()
    {
        if (is_null(self::$disk)) {
            self::$disk = parent::disk(self::STORAGE);
        }
        return self::$disk;
    }

    public static function disk()
    {
        return self::init();
    }
}
