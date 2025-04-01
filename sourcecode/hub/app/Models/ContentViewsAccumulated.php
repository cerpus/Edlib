<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ContentViewsAccumulatedFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentViewsAccumulated extends Model
{
    /** @use HasFactory<ContentViewsAccumulatedFactory> */
    use HasFactory;
    use HasUuids;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    protected $table = 'content_views_accumulated';

    protected $attributes = [
        'view_count' => 0,
    ];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<LtiPlatform, $this>
     */
    public function ltiPlatform(): BelongsTo
    {
        return $this->belongsTo(LtiPlatform::class);
    }
}
