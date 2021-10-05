<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class H5POption extends Model
{
    protected $table = 'h5p_options';

    protected $primaryKey = 'option_id';

    protected $guarded = [];

    public $timestamps = false;

    const NDLA_CUSTOM_CSS_TIMESTAMP = "NDLA_CUSTOM_CSS_TIMESTAMP";
}
