<?php

declare(strict_types=1);

namespace App\Models;

use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Cerpus\EdlibResourceKit\Oauth1\CredentialStoreInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Random\Randomizer;

class LtiPlatform extends Model
{
    use HasFactory;
    use HasUlids;

    protected $hidden = [
        'secret',
    ];

    protected $fillable = [
        'name',
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

    public static function createOauth1CredentialsStore(): CredentialStoreInterface
    {
        return new class () implements CredentialStoreInterface {
            public function findByKey(string $key): Credentials|null
            {
                return LtiPlatform::where('key', $key)
                    ->first()
                    ?->getOauth1Credentials();
            }
        };
    }
}
