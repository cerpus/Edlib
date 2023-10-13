<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $title
 * @property string $summary
 * @property string $description
 * @property string $license
 * @property string $screenshots
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
        return $folderName ?
            \H5PCore::libraryToFolderName([
                'machineName' => $this->name,
                'majorVersion' => $this->major_version,
                'minorVersion' => $this->minor_version,
            ]) :
            \H5PCore::libraryToString([
                'machineName' => $this->name,
                'majorVersion' => $this->major_version,
                'minorVersion' => $this->minor_version,
            ]);
    }
}
