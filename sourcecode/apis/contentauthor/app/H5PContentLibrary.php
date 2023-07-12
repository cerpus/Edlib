<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $content_id
 * @property int $library_id
 * @property string $dependency_type
 * @property int $weight
 * @property int $drop_css
 */

class H5PContentLibrary extends Model
{
    use HasFactory;

    protected $table = 'h5p_contents_libraries';

    protected $guarded = [];

    public $timestamps = false;
}
