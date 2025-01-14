<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\ContextDeleting;
use App\Support\HasUlidsFromCreationDate;
use Database\Factories\ContextFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A "context" is used to grant access based on externally defined criteria.
 */
class Context extends Model
{
    /** @use HasFactory<ContextFactory> */
    use HasFactory;
    use HasUlidsFromCreationDate;

    public const UPDATED_AT = null;

    protected $perPage = 100;

    protected $fillable = [
        'name',
    ];

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'deleting' => ContextDeleting::class,
    ];

    /**
     * @return BelongsToMany<Content, $this>
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_context');
    }

    /**
     * @return BelongsToMany<LtiPlatform, $this>
     */
    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(LtiPlatform::class, 'lti_platform_context');
    }
}
