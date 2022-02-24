<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int h5p_id
 * @property string email
 *
 * @method static self where($column, $operator = null, $value = null, $boolean = 'and')
 */
class H5PCollaborator extends Model
{
    use HasFactory;

    protected $table = 'cerpus_contents_shares';

    public function setUpdatedAt($value)
    {
        // Do nothing.
    }

}
