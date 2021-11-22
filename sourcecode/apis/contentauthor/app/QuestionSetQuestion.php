<?php

namespace App;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSetQuestion extends Model
{
    use HasFactory;
    use UuidForKey;

    public function questionset()
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function answers()
    {
        return $this->hasMany(QuestionSetQuestionAnswer::class, 'question_id')->ordered();
    }
}
