<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContentEdlib2UsageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
