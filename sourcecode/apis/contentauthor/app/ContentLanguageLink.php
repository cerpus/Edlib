<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
