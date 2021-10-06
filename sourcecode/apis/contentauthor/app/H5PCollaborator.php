<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class H5PCollaborator extends Model
{
    protected $table = 'cerpus_contents_shares';

    public function setUpdatedAt($value)
    {
        // Do nothing.
    }

}
