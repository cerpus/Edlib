<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContentEdlib2UsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Represents imported usage IDs from Edlib 2, and newly created ones used by
 * NDLA. Should not be used for other purposes.
 */
class ContentEdlib2Usage extends Model
{
    /** @use HasFactory<ContentEdlib2UsageFactory> */
    use HasFactory;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $attributes = [
        'edlib2_usage_id' => null,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $usage) {
            $usage->attributes['edlib2_usage_id'] ??= (string) Str::uuid();
        });

        // Clear cache when usage records are created, updated, or deleted (only if caching is enabled)
        if (config('cache.edlib2_usage_lookups.enabled')) {
            static::saved(function (self $usage) {
                $usage->clearContentCache();
            });

            static::deleted(function (self $usage) {
                $usage->clearContentCache();
            });
        }
    }

    /**
     * Clear the cache for the content associated with this usage ID
     */
    private function clearContentCache(): void
    {
        if (!$this->edlib2_usage_id) {
            return;
        }

        $keyPrefix = config('cache.edlib2_usage_lookups.key_prefix');
        Cache::forget($keyPrefix . $this->edlib2_usage_id);
    }

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function getRouteKeyName(): string
    {
        return 'edlib2_usage_id';
    }
}
