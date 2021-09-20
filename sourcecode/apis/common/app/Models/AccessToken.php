<?php

namespace App\Models;

use App\Models\Traits\UuidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function base64_encode;
use function random_bytes;

/**
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
