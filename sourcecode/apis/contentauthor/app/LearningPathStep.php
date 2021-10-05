<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LearningPathStep extends Model
{
    protected $fillable = [
        'id',
        'learning_path_id',
        'title',
        'order',
        'json'
    ];

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
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
