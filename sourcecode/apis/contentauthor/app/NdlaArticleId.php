<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $language
 * @property string $type
 * @property string $json
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

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
