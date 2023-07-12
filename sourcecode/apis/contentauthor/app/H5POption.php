<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $option_id
 * @property ?string $option_name
 * @property ?string $option_value
 * @property string $autoload
 */

class H5POption extends Model
{
    protected $table = 'h5p_options';

    protected $primaryKey = 'option_id';

    protected $guarded = [];

    public $timestamps = false;

    public const NDLA_CUSTOM_CSS_TIMESTAMP = "NDLA_CUSTOM_CSS_TIMESTAMP";
}
