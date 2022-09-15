<?php

namespace App\Libraries\DataObjects;

class ContentStorageSettings
{
    public const STORAGE_PREFIX = 'h5p/';
    public const EDITOR_PATH = "editor/";
    public const CONTENT_PATH = "content/%s/";
    public const CONTENT_LOCAL_PATH = "%ss/%s.%s";
    public const CONTENT_FULL_PATH = self::CONTENT_PATH . self::CONTENT_LOCAL_PATH;
    public const FILE_PATH = "%s%ss";
    public const EXPORT_DIR = 'exports/';
    public const EXPORT_PATH = self::EXPORT_DIR . '%s';
    public const TEMP_DIR = 'temp/';
    public const TEMP_PATH = self::TEMP_DIR . '%s';
    public const TEMP_CONTENT_PATH = self::TEMP_PATH . '/content';
    public const LIBRARY_DIR = 'libraries/';
    public const LIBRARY_PATH = self::LIBRARY_DIR . '%s';
    public const UPGRADE_SCRIPT_PATH = self::LIBRARY_PATH . "/upgrades.js";
    public const PRESAVE_SCRIPT_PATH = self::LIBRARY_PATH . "/presave.js";
    public const CACHEDASSETS_DIR = 'cachedassets/';
    public const CACHEDASSETS_JS_PATH = self::CACHEDASSETS_DIR . '%s.js';
    public const CACHEDASSETS_CSS_PATH = self::CACHEDASSETS_DIR . '%s.css';
    public const LIBRARY_JSONFILE_PATH = self::LIBRARY_PATH . "/library.json";
    public const LIBRARY_VERSION_PREFIX = '?ver=%s.%s.%s';

    public const GAMES_DIR = 'games/';
    public const GAMES_PATH = self::GAMES_DIR . '%s';
    public const GAMES_FILE = self::GAMES_PATH . '/%s';

    public const ARTICLE_DIR = 'article-uploads/';
    public const ARTICLE_PATH = self::ARTICLE_DIR . '%s';
    public const ARTICLE_FILE = self::ARTICLE_PATH . '/%s';
}
