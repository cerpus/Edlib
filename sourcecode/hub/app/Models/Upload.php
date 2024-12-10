<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Upload extends Model
{
    use HasUlids;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    /**
     * @return HasMany<ContentVersion, $this>
     */
    public function contentVersions(): HasMany
    {
        return $this->hasMany(ContentVersion::class, 'icon_image_id');
    }

    public function getUrl(): string
    {
        return Storage::disk('uploads')->url($this->path);
    }
}
