<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Norgesfilm extends Model
{
    protected $guarded = [];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
