<?php

namespace App;

use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string id
 * @property string answer_text
 * @property int correct
 */

class QuestionSetQuestionAnswer extends Model
{
    use HasFactory;
    use UuidForKey;

    public function question()
    {
        return $this->belongsTo(QuestionSetQuestion::class, 'question_id')->ordered();
    }

    public function scopeOrdered($query)
    {
        $query->orderBy('order');
        return $query;
    }
}
