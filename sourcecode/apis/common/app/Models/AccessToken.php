<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function base64_encode;
use function random_bytes;

/**
 * @property string $id
 * @property string $name
 * @property string $token
 * @property string $application_id
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class AccessToken extends Model
{
    protected $keyType = 'uuid';
    protected $hidden = [
        'token',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function () {
            $this->token = base64_encode(random_bytes(32));
        });
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
