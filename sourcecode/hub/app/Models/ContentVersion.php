<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ContentVersion extends Model
{
    use HasFactory;
    use HasUuids;

    public const UPDATED_AT = null;

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(self::class, 'parent_version_id');
    }
}
