<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $answer_text
 * @property int $correct
 * @property string $question_id
 * @property string $image
 * @property int $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $external_reference
 * @property boolean $is_private
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
