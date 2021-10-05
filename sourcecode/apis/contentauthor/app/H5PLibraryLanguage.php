<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class H5PLibraryLanguage extends Model
{
    protected $table = 'h5p_libraries_languages';

    protected $fillable = [
        'library_id',
        'language_code',
        'translation'
    ];

    public $timestamps = false;

    public function library()
    {
        return $this->belongsTo(H5PLibrary::class);
    }

    public function scopeFromLibrary($query, $library)
    {
        list($machineName, $majorVersion, $minorVersion) = array_values($library);
        $query->whereHas('library', function ($query) use ($machineName, $majorVersion, $minorVersion) {
            $query->fromLibrary([$machineName, $majorVersion, $minorVersion]);
        });
    }
}
