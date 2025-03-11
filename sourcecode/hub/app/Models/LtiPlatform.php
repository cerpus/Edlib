<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentRole;
use App\Events\LtiPlatformDeleting;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Database\Factories\LtiPlatformFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Random\Randomizer;

use function assert;

class LtiPlatform extends Model
{
    /** @use HasFactory<LtiPlatformFactory> */
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    protected $attributes = [
        'enable_sso' => false,
        'authorizes_edit' => false,
    ];

    protected $hidden = [
        'secret',
    ];

    protected $fillable = [
        'name',
        'enable_sso',
        'authorizes_edit',
    ];

    protected $casts = [
        'enable_sso' => 'boolean',
        'authorizes_edit' => 'boolean',
    ];

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'deleting' => LtiPlatformDeleting::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ltiPlatform): void {
            // Note: do not cast "key" to uuid. Keys are opaque identifiers
            // without any semantic meaning, and there's no point potentially
            // digging ourselves into a hole by placing any restriction on them.
            $ltiPlatform->key ??= Str::uuid()->toString();

            $ltiPlatform->secret ??= base64_encode(
                app()->make(Randomizer::class)->getBytes(24),
            );
        });
    }

    /**
     * @return BelongsToMany<Context, $this>
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class, 'lti_platform_context')
            ->withPivot('role')
            ->using(LtiPlatformContext::class);
    }

    public function hasContextWithMinimumRole(Context $context, ContentRole $role): bool
    {
        foreach ($this->contexts as $platformContext) {
            if ($platformContext->is($context)) {
                // @phpstan-ignore property.notFound
                $ltiPlatformRole = $platformContext->pivot->role;
                assert($ltiPlatformRole instanceof ContentRole);

                // Context cannot be added more than once, so we return here.
                return $ltiPlatformRole->grants($role);
            }
        }

        return false;
    }

    public function getOauth1Credentials(): Credentials
    {
        return new Credentials($this->key, $this->secret);
    }
}
