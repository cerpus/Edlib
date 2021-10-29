<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = "content";

    protected $visible = [
        'id',
        'site',
        'content_id',
        'name',
        'licenses',
    ];

    public function licenses() {
        return $this->hasMany("App\Models\ContentLicense");
    }

    protected static function boot() {
        parent::boot();

        static::deleting(function (Content $content) {
            $content->licenses()->delete();
        });
    }
}
