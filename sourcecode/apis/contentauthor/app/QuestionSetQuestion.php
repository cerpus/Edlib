<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @property string $id
 * @property string $question_text
 * @property string $image
 * @property string $question_set_id
 * @property int $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?string $external_reference
 * @property boolean $is_private
 *
 * @property Collection<QuestionSetQuestionAnswer> $answers
 */

class QuestionSetQuestion extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @return BelongsTo<QuestionSet, self>
     */
    public function questionset(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order');
    }

    /**
     * @return HasMany<QuestionSetQuestionAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionSetQuestionAnswer::class, 'question_id')->ordered();
    }
}
