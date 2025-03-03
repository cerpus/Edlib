<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $answer_text
 * @property int $correct
 * @property string|null $image
 * @property int $order
 */

class QuestionSetQuestionAnswer extends Model
{
    use HasFactory;
    use HasUuids;

    public function question()
    {
        return $this->belongsTo(QuestionSetQuestion::class, 'question_id')->ordered();
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }
}
