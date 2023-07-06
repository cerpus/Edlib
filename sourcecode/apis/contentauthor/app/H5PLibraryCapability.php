<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;

class H5PLibraryCapability extends Model
{
    protected $table = 'h5p_library_capabilities';

    protected $appends = ['title', 'description'];

    /**
     * @return BelongsTo<H5PLibrary, self>
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('enabled', 1);
    }

    public function getTitleAttribute(): string
    {
        $locale = Lang::getLocale();
        $trans = LibraryDescription::where('locale', $locale)->where('library_id', $this->library_id)->first();
        if (is_null($trans)) {
            if (empty($this->title)) {
                return '';
            }
            return $this->title;
        }
        return $trans->title;
    }

    public function getDescriptionAttribute(): string
    {
        $locale = Lang::getLocale();
        $trans = LibraryDescription::where('locale', $locale)->where('library_id', $this->library_id)->first();
        if (is_null($trans)) {
            return '';
        }
        return $trans->description;
    }
}
