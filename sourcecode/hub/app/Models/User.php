<?php

declare(strict_types=1);

namespace App\Models;

use App\Events\UserSaved;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasFactory;
    use HasUlids;

    public const SOCIAL_PROVIDERS = [
        'auth0',
        'facebook',
        'google',
    ];

    protected $casts = [
        'admin' => 'boolean',
        'debug_mode' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'locale',
        'debug_mode',
        'email',
        'theme',
    ];

    protected $visible = [
        'id',
        'name',
        'admin',
        'created_at',
        'updated_at',
        'locale',
        'debug_mode',
        'theme',
    ];

    /**
     * @var array<string, string>
     */
    protected $dispatchesEvents = [
        'saved' => UserSaved::class,
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'admin' => false,
        'debug_mode' => false,
    ];

    public function getAuthIdentifierName(): string
    {
        return 'email';
    }

    /**
     * Create an account from social login details.
     *
     * We want the following to happen:
     *
     * - If there is an existing account with the social ID, we use that account
     *   no matter if the email address matches.
     * - Otherwise, if there is an existing account with the same email address,
     *   we use that account, and update its social ID.
     * - Otherwise, we create a new user account with the name, email, and ID
     *   from the provided credentials.
     */
    public static function fromSocial(string $provider, SocialiteUser $details): self
    {
        if (!in_array($provider, self::SOCIAL_PROVIDERS, true)) {
            throw new InvalidArgumentException('Unknown social provider');
        }

        $user = self::firstWhere("{$provider}_id", $details->getId());

        if ($user) {
            return $user;
        }

        $user = self::firstWhere('email', $details->getEmail());

        if ($user) {
            $user->forceFill(["{$provider}_id" => $details->getId()]);
            $user->save();

            return $user;
        }

        return self::forceCreate([
            'name' => $details->getName(),
            'email' => $details->getEmail(),
            "{$provider}_id" => $details->getId(),
        ]);
    }
}
