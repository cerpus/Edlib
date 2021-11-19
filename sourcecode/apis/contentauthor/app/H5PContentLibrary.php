<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class H5PContentLibrary extends Model
{
    use HasFactory;

    protected $table = 'h5p_contents_libraries';

    protected $guarded = [];

    public $timestamps = false;

}
