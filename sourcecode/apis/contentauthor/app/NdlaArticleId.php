<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NdlaArticleId extends Model
{
    protected $fillable = ['id', 'title', 'type', 'language', 'json'];

    public function getJsonAttribute($value)
    {
        return json_decode($value);
    }

    public function setJsonAttribute($value): void
    {
        $this->attributes['json'] = json_encode($value);
    }

    /**
     * @return HasMany<NdlaArticleImportStatus>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(NdlaArticleImportStatus::class, 'ndla_id');
    }
}
