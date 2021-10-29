<?php

namespace App\Models;

use App\Models\Traits\UuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function base64_encode;
use function random_bytes;

/**
 * App\Models\AccessToken
 *
 * @property string $id
 * @property string $name
 * @property string $token
 * @property string $application_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application $application
 * @method static \Database\Factories\AccessTokenFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessToken whereUpdatedAt($value)
 * @mixin IdeHelperAccessToken
 */
class AccessToken extends Model
{
    use HasFactory;
    use UuidKey;

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'token',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!isset($this->token)) {
            $this->token = base64_encode(random_bytes(36));
        }
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
