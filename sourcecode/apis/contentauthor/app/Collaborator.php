<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Collaborator extends Model
{
    use HasFactory;

    protected $fillable = ['email'];

    public function collaboratable(): MorphTo
    {
        return $this->morphTo();
    }
}
