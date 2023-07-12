<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property static $name
 * @property int $major_version
 * @property int $minor_version
 * @property int $patch_version
 * @property int $h5p_major_version
 * @property int $h5p_minor_version
 * @property string $title
 * @property string $summary
 * @property string $description
 * @property string $icon
 * @property int $is_recommended
 * @property int $popularity
 * @property string $screenshots
 * @property string $license
 * @property string $example
 * @property string $tutorial
 * @property string $keywords
 * @property string $categories
 * @property string $owner
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class H5PLibrariesHubCache extends Model
{
    protected $table = 'h5p_libraries_hub_cache';

    protected $guarded = [];

    public function getMachineNameAttribute()
    {
        return $this->name;
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->getTimestamp();
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->getTimestamp();
    }

    /**
     * @return HasMany<H5PLibrary>
     */
    public function libraries(): HasMany
    {
        return $this->hasMany(H5PLibrary::class, 'name', 'name');
    }

    public function getLibraryString($folderName = false)
    {
        return \H5PCore::libraryToString([
            'machineName' => $this->name,
            'majorVersion' => $this->major_version,
            'minorVersion' => $this->minor_version,
        ], $folderName);
    }
}
