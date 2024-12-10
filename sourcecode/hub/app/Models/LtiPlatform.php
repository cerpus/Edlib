<?php

declare(strict_types=1);

namespace App\Models;

use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Database\Factories\LtiPlatformFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Random\Randomizer;

class LtiPlatform extends Model
{
    /** @use HasFactory<LtiPlatformFactory> */
    use HasFactory;
    use HasUlids;
    use SoftDeletes;

    /** @var array<string, mixed> */
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

    protected static function booted(): void
    {
        static::creating(function (self $ltiPlatform): void {
            // Note: do not cast "key" to uuid. Keys are opaque identifiers
            // without any semantic meaning, and there's no point potentially
            // digging ourselves into a hole by placing any restriction on them.
            $ltiPlatform->key ??= Str::uuid()->toString();

            $ltiPlatform->secret ??= base64_encode(
                app()->make(Randomizer::class)->getBytes(24)
            );
        });
    }

    public function getOauth1Credentials(): Credentials
    {
        return new Credentials($this->key, $this->secret);
    }
}
