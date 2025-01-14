<?php

declare(strict_types=1);

namespace App\Models;

use App\Configuration\Themes;
use App\Events\UserSaved;
use BadMethodCallException;
use Database\Factories\UserFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;
use SensitiveParameter;

use function config;

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
    ];

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

    /**
     * Accessor for 'theme` attribute
     * @return Attribute<string, null> Selected theme, or default theme if unset or not a valid theme
     */
    protected function theme(): Attribute
    {
        return Attribute::make(
            get: function(string|null $value) {
                $themes = new Themes();
                return in_array($value, $themes->all()) ? $value : $themes->getDefault();
            },
        );
    }
}
