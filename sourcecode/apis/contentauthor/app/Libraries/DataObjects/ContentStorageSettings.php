<?php

namespace App\Libraries\DataObjects;

class ContentStorageSettings
{
    const STORAGE_PREFIX = 'h5p/';
    const EDITOR_PATH = "editor/";
    const CONTENT_PATH = "content/%s/";
    const CONTENT_LOCAL_PATH = "%ss/%s.%s";
    const CONTENT_FULL_PATH = self::CONTENT_PATH . self::CONTENT_LOCAL_PATH;
    const FILE_PATH = "%s%ss";
    const EXPORT_DIR = 'exports/';
    const EXPORT_PATH = self::EXPORT_DIR . '%s';
    const TEMP_DIR = 'temp/';
    const TEMP_PATH = self::TEMP_DIR . '%s';
    const TEMP_CONTENT_PATH = self::TEMP_PATH . '/content';
    const LIBRARY_DIR = 'libraries/';
    const LIBRARY_PATH = self::LIBRARY_DIR . '%s';
    const UPGRADE_SCRIPT_PATH = self::LIBRARY_PATH . "/upgrades.js";
    const PRESAVE_SCRIPT_PATH = self::LIBRARY_PATH . "/presave.js";
    const CACHEDASSETS_DIR = 'cachedassets/';
    const CACHEDASSETS_JS_PATH = self::CACHEDASSETS_DIR . '%s.js';
    const CACHEDASSETS_CSS_PATH = self::CACHEDASSETS_DIR . '%s.css';
    const LIBRARY_JSONFILE_PATH = self::LIBRARY_PATH . "/library.json";
    const LIBRARY_VERSION_PREFIX = '?ver=%s.%s.%s';

    const GAMES_DIR = 'games/';
    const GAMES_PATH = self::GAMES_DIR . '%s';
    const GAMES_FILE = self::GAMES_PATH . '/%s';

    const ARTICLE_DIR = 'article-uploads/';
    const ARTICLE_PATH = self::ARTICLE_DIR . '%s';
    const ARTICLE_FILE = self::ARTICLE_PATH . '/%s';
}
