<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentVersion extends Model
{
    use HasFactory;
    use HasUlids;

    public const UPDATED_AT = null;

    /** @var string[] */
    protected $fillable = [
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    /** @var string[] */
    protected $touches = [
        'content',
    ];

    /**
     * @return BelongsTo<Content, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<LtiResource, self>
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(LtiResource::class, 'lti_resource_id');
    }

    /**
     * @param Builder<self> $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('published', true);
    }
}
