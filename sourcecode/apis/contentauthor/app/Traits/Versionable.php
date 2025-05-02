<?php

namespace App\Traits;

use App\Libraries\Versioning\VersionableObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property VersionableObject::PURPOSE_* $version_purpose
 * @mixin Model
 */
trait Versionable
{
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function getVersionPurpose(): string
    {
        return $this->version_purpose;
    }
}
