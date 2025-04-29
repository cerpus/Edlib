<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasUlidsFromCreationDate;
use Database\Factories\ContentLockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentLock extends Model
{
    /** @use HasFactory<ContentLockFactory> */
    use HasFactory;
    use HasUlidsFromCreationDate;

    public const TTL_SECONDS = 90;
    public const REFRESH_TIME_SECONDS = 30;

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Builder<ContentLock> $query
     */
    public function scopeInactive(Builder $query): void
    {
        $query->where('updated_at', '<', now()->subSeconds(self::TTL_SECONDS));
    }

    /**
     * @param Builder<ContentLock> $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('updated_at', '>=', now()->subSeconds(self::TTL_SECONDS));
    }
}
