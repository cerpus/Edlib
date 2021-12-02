<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\GdprRequest
 *
 * @property int $id
 * @property string $application_id
 * @property string|null $requestId
 * @property string $userId
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application $application
 * @method static \Database\Factories\GdprRequestFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereRequestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GdprRequest whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $request_id
 * @property string $user_id
 * @mixin IdeHelperGdprRequest
 */
class GdprRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'user_id',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function gdprRequestCompletedSteps(): HasMany
    {
        return $this->hasMany(GdprRequestCompletedStep::class);
    }
}
