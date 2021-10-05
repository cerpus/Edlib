<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class H5PLibrariesCachedAssets extends Model
{
    protected $table = 'h5p_libraries_cachedassets';

    protected $fillable = ['hash', 'library_id'];
}
