<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LtiToolEditMode;
use App\Enums\LtiVersion;
use BadMethodCallException;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Database\Factories\LtiToolFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function assert;

class LtiTool extends Model
{
    /** @use HasFactory<LtiToolFactory> */
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    protected $perPage = 12;

    /** @var mixed[] */
    protected $attributes = [
        'lti_version' => LtiVersion::Lti1_1,
        'send_name' => false,
        'send_email' => false,
        'proxy_launch' => false,
        'edit_mode' => LtiToolEditMode::Replace,
    ];

    protected $casts = [
        'lti_version' => LtiVersion::class,
        'edit_mode' => LtiToolEditMode::class,
        'send_name' => 'boolean',
        'send_email' => 'boolean',
        'proxy_launch' => 'boolean',
    ];

    protected $hidden = [
        'consumer_secret',
    ];

    protected $fillable = [
        'name',
        'creator_launch_url',
        'consumer_key',
        'consumer_secret',
        'send_name',
        'send_email',
        'proxy_launch',
        'edit_mode',
    ];

    /**
     * @return HasMany<ContentVersion>
     */
    public function contentVersions(): HasMany
    {
        return $this->hasMany(ContentVersion::class);
    }

    /**
     * @return HasMany<LtiToolExtra>
     */
    public function extras(): HasMany
    {
        return $this->hasMany(LtiToolExtra::class);
    }

    public function getOauth1Credentials(): Credentials
    {
        assert($this->consumer_key !== null && $this->consumer_secret !== null);

        if ($this->lti_version !== LtiVersion::Lti1_1) {
            throw new BadMethodCallException(
                'Can only get OAuth 1.0 credentials for LTI 1.1 tools',
            );
        }

        return new Credentials(
            $this->consumer_key,
            $this->consumer_secret,
        );
    }
}
