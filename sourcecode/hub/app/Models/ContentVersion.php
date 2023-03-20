<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
     * @return HasOne<self>
     */
    public function parent(): HasOne
    {
        return $this->hasOne(self::class, 'parent_version_id');
    }
}
