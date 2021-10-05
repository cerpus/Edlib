<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LearningPath extends Model
{
    protected $fillable = ['id', 'title', 'json'];

    public function steps()
    {
        return $this->hasMany(LearningPathStep::class)->orderBy('order');
    }

    public function getJsonAttribute($value)
    {
        return json_decode($value);
    }

    public function setJsonAttribute($value)
    {
        $this->attributes['json'] = json_encode($value);
    }
}
