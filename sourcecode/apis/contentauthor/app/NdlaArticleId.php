<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NdlaArticleId extends Model
{
    protected $fillable = ['id', 'title', 'type', 'language', 'json'];

    public function getJsonAttribute($value)
    {
        return json_decode($value);
    }

    public function setJsonAttribute($value)
    {
        $this->attributes['json'] = json_encode($value);
    }

    public function messages()
    {
        return $this->hasMany(NdlaArticleImportStatus::class, 'ndla_id');
    }
}
