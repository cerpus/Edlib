<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $question_text
 * @property string|null $image
 * @property int $order
 * @property string $external_reference
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
