<?php

namespace App\Models;

use App\Models\Traits\UuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperApplication
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

    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (Application $application) {
            $application->id = Uuid::uuid4()->toString();
        });
    }
}
