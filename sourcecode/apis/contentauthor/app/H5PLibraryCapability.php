<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;

class H5PLibraryCapability extends Model
{
    protected $table = 'h5p_library_capabilities';

    protected $appends = ['title', 'description'];

    public function library()
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    public function scopeActive($query)
    {
        return $query->where('enabled', 1);
    }

    public function getTitleAttribute()
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

    public function getDescriptionAttribute()
    {
        $locale = Lang::getLocale();
        $trans = LibraryDescription::where('locale', $locale)->where('library_id', $this->library_id)->first();
        if (is_null($trans)) {
            return '';
        }
        return $trans->description;
    }
}
