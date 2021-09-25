<?php

namespace App\Models;

use App\Models\Traits\UuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * App\Models\Application
 *
 * @mixin IdeHelperApplication
 * @property string $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AccessToken[] $accessTokens
 * @property-read int|null $access_tokens_count
 * @method static \Database\Factories\ApplicationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereUpdatedAt($value)
 */
class Application extends Model
{
    use HasFactory;
    use UuidKey;

    protected $fillable = [
        'name',
    ];

    public function accessTokens(): HasMany
    {
        return $this->hasMany(AccessToken::class);
    }

    public function resourceCollaborators(): HasMany
    {
        return $this->hasMany(ResourceCollaborator::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (Application $application) {
            $application->id = Uuid::uuid4()->toString();
        });
    }
}
