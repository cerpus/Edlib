<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\GdprRequestCompletedStep
 *
 * @property int $id
 * @property int $gdpr_request_id
 * @property string $service_name
 * @property string $step_name
 * @property string|null $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\GdprRequest $gdprRequest
 * @method static \Database\Factories\GdprRequestCompletedStepFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep query()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereGdprRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereStepName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequestCompletedStep whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperGdprRequestCompletedStep
 */
class GdprRequestCompletedStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_name',
        'step_name',
        'message',
    ];

    public function gdprRequest(): BelongsTo
    {
        return $this->belongsTo(GdprRequest::class);
    }
}
