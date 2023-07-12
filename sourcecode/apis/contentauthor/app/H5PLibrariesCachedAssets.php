<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $library_id
 * @property string $hash
 */

class H5PLibrariesCachedAssets extends Model
{
    protected $table = 'h5p_libraries_cachedassets';

    protected $fillable = ['hash', 'library_id'];
}
