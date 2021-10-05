<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collaborator extends Model
{
    protected $fillable = ['email'];

    public function collaboratable()
    {
        return $this->morphTo();
    }
}
