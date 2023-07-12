<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $filename
 * @property string $user_id
 * @property string $state
 * @property ?string $file_hash
 * @property ?string $external_reference
 * @property int $content_id
 * @property ?string $requestId
 * @property ?string $params
 * @property ?Carbon $process_start
 *
 * @method static null|Builder ofFileUploadFromRequestId($requestId)
 * @method static null|Builder ofFileUploadFromContent($contentId)
 */
class H5PFile extends Model
{
    public const FILE_TEMPORARY = "temporary";
    public const FILE_CLONEFILE = "clonefile";
    public const FILE_READY = "ready";
    public const FILE_FAILED = "failed";

    protected $table = 'h5p_files';

    protected $guarded = ['id'];

    public function getParamsAttribute($value)
    {
        if (!empty($value)) {
            return json_decode($value);
        }
        return $value;
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfFileUploadFromRequestId(Builder $query, $requestId): void
    {
        $query->where('requestId', $requestId);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfFileUploadFromContent(Builder $query, int $contentId): void
    {
        $query->where('content_id', $contentId);
    }

    protected function deleteContentPendingUpload(int $contentId, string $filePath): void
    {
        self::ofFileUploadFromContent($contentId)
            ->get()
            ->filter(function ($file) use ($filePath) {
                $params = $file->params;
                return !empty($params) && $file->state === H5PFile::FILE_CLONEFILE && $params->to === $filePath;
            })
            ->each(function ($file) {
                $file->delete();
            });
    }
}
