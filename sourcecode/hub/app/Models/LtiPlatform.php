<?php

declare(strict_types=1);

namespace App\Models;

use App\Lti\Oauth1\Oauth1Credentials;
use App\Lti\Oauth1\Oauth1CredentialStoreInterface;
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

    public function getOauth1Credentials(): Oauth1Credentials
    {
        return new Oauth1Credentials($this->key, $this->secret);
    }

    public static function createOauth1CredentialsStore(): Oauth1CredentialStoreInterface
    {
        return new class () implements Oauth1CredentialStoreInterface {
            public function findByKey(string $key): Oauth1Credentials|null
            {
                return LtiPlatform::where('key', $key)
                    ->first()
                    ?->getOauth1Credentials();
            }
        };
    }
}