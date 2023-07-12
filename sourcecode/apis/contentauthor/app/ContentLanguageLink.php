<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $language_code
 * @property string $content_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * */

class ContentLanguageLink extends Model
{
    protected $fillable = ['main_content_id', 'link_content_id', "language_code", "content_type"];

    /**
     * @param Builder<self> $query
     */
    public function scopeOfContentType(Builder $query, $contentType): void
    {
        $query->where("content_type", $contentType);
    }
}
