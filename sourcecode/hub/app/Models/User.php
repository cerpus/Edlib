<?php

declare(strict_types=1);

namespace App\Models;

use App\Configuration\Features;
use App\Events\UserSaved;
use BadMethodCallException;
use Database\Factories\UserFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;
use SensitiveParameter;

use function config;
use function hash_equals;
use function time;
use function url;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    /** @use HasFactory<UserFactory> */
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
        'email_verified' => 'boolean',
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

    protected $attributes = [
        'locale' => 'en',
        'admin' => false,
        'debug_mode' => false,
        // If false, emails are sent upon creating the user. Aside from the
        // sign-up page, this is probably undesirable, so having it true is the
        // safe default.
        'email_verified' => true,
    ];

    public function setEmailAttribute(string $email): void
    {
        $this->attributes['email'] = strtolower($email);

        if ($this->exists && $email !== $this->getOriginal('email')) {
            $this->attributes['email_verified'] = false;
        }
    }

    public function getApiKey(): string
    {
        return $this->id;
    }

    public function getApiSecret(): string
    {
        $key = config('app.key') ?? throw new RuntimeException('missing app key');
        $userId = $this->id ?? throw new BadMethodCallException('missing id');
        $password = $this->password ?? '';

        return base64_encode(hash(
            'sha256',
            sprintf('%s.%s.%s', $key, $userId, $password),
            binary: true,
        ));
    }

    public function validateApiSecret(#[SensitiveParameter] string $secret): bool
    {
        return hash_equals($this->getApiSecret(), $secret);
    }

    public function getApiAuthorization(): string
    {
        return 'Authorization: Basic ' .
            base64_encode($this->getApiKey() . ':' . $this->getApiSecret());
    }

    public function checkVerificationDetails(string $hash, int $time): bool
    {
        return hash_equals(hash('sha256', $time . '@' . $this->email), $hash);
    }

    public function makeVerificationLink(): string
    {
        $time = time();

        return url()->temporarySignedRoute('user.verify-email', 3600, [
            'hash' => hash('sha256', $time . '@' . $this->email),
            'time' => time(),
        ]);
    }

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

        $features = app()->make(Features::class);

        $user = self::firstWhere("{$provider}_id", $details->getId());

        if (!$user) {
            $user = self::firstWhere('email', $details->getEmail());
        }

        if ($user) {
            $user->forceFill(["{$provider}_id" => $details->getId()]);
            $user->save();
        } else {
            $user = self::forceCreate([
                'name' => $details->getName(),
                'email' => $details->getEmail(),
                "{$provider}_id" => $details->getId(),
                'email_verified' => $features->socialUsersAreVerified(),
            ]);
        }

        if (!$user->email_verified && $features->socialUsersAreVerified()) {
            $user->email_verified = true;
            $user->save();
        }

        return $user;
    }
}
