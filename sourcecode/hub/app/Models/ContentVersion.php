<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;
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

    protected $casts = [
        'published' => 'boolean',
    ];

    /** @var string[] */
    protected $touches = [
        'content',
    ];

    public function getTitle(): string
    {
        return $this->resource?->title
            ?? throw new DomainException('The content version has no title');
    }

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

    /**
     * @param Builder<self> $query
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('published', false);
    }
}
