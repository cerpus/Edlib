<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static null|Builder ofFileUploadFromRequestId($requestId)
 * @method static null|Builder ofFileUploadFromContent($contentId)
 */
class H5PFile extends Model
{
    const FILE_TEMPORARY = "temporary";
    const FILE_CLONEFILE = "clonefile";
    const FILE_READY = "ready";
    const FILE_FAILED = "failed";

    protected $table = 'h5p_files';

    protected $guarded = ['id'];

    public function getParamsAttribute($value){
        if (!empty($value)){
            return json_decode($value);
        }
        return $value;
    }

    /**
     * @param Builder $query
     * @param string $requestId
     */
    protected function getFileUploadStatusFromRequestIdScope($query, $requestId){
        $query->where('requestId', $requestId);
    }

    /**
     * @param Builder $query
     * @param string $requestId
     */
    public function scopeOfFileUploadFromRequestId($query, $requestId) {
        return $this->getFileUploadStatusFromRequestIdScope($query, $requestId);
    }

    /**
     * @param Builder $query
     * @param int $contentId
     */
    protected function getFileUploadStatusFromContentScope($query, $contentId){
        $query->where('content_id', $contentId);
    }

    /**
     * @param Builder $query
     * @param int $contentId
     */
    public function scopeOfFileUploadFromContent($query, $contentId) {
        return $this->getFileUploadStatusFromContentScope($query, $contentId);
    }

    /**
     * @param int $contentId
     * @param string $filePath
     */
    protected function deleteContentPendingUpload($contentId, $filePath) {
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
