<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $dependency_type
 * @property-read H5PLibrary $library
 * @property-read H5PLibrary $requiredLibrary
 */
class H5PLibraryLibrary extends Model
{
    protected $table = 'h5p_libraries_libraries';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return BelongsTo<H5PLibrary, $this>
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    /**
     * @return HasOne<H5PLibrary, $this>
     */
    public function requiredLibrary(): HasOne
    {
        return $this->hasOne(H5PLibrary::class, 'id', 'required_library_id');
    }
}
