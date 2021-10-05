<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContentLanguageLink extends Model
{
    protected $fillable = ['main_content_id', 'link_content_id', "language_code", "content_type"];

    public function scopeOfContentType($query, $contentType)
    {
        return $query->where("content_type", $contentType);
    }
}
