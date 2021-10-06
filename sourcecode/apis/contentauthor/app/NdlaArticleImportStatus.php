<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NdlaArticleImportStatus extends Model
{
    // Log levels from Monolog
    // https://github.com/Seldaek/monolog/blob/master/doc/01-usage.md#log-levels
    const LOG_LEVEL_DEBUG = 100;
    const LOG_LEVEL_INFO = 200;
    const LOG_LEVEL_NOTICE = 250;
    const LOG_LEVEL_WARNING = 300;
    const LOG_LEVEL_ERROR = 400;
    const LOG_LEVEL_CRITICAL = 500;
    const LOG_LEVEL_ALERT = 550;
    const LOG_LEVEL_EMERGENCY = 600;

    protected $fillable = ['ndla_id', 'message', 'import_id', 'log_level'];

    public static function byNdlaId($id)
    {
        return self::where('ndla_id', $id)->get();
    }

    public static function mostRecent()
    {
        return self::orderBy('id', 'desc')->first();
    }

    public static function mostRecentStatuses()
    {
        return self::orderBy('id', 'desc')->limit(500)->get();
    }

    public function ndlaArticle()
    {
        return $this->belongsTo(NdlaArticleId::class, 'ndla_id');
    }

    public static function addStatus($id, $message, $importId = null, $logLevel = self::LOG_LEVEL_DEBUG)
    {
        $status = [
            'ndla_id' => $id,
            'message' => $message,
            'log_level' => $logLevel,
        ];

        if ($importId) {
            $status['import_id'] = $importId;
        }

        $response = self::create($status);

        return $response;
    }

    public static function logDebug($id, $message, $importId = null)
    {
        return self::addStatus($id, $message, $importId, self::LOG_LEVEL_DEBUG);
    }

    public static function logError($id, $message, $importId = null)
    {
        return self::addStatus($id, $message, $importId, self::LOG_LEVEL_ERROR);
    }
}
