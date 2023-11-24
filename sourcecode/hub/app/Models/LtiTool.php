<?php

declare(strict_types=1);

namespace App\Models;

use BadMethodCallException;
use Cerpus\EdlibResourceKit\Oauth1\Credentials;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use function assert;

class LtiTool extends Model
{
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    /** @var mixed[] */
    protected $attributes = [
        'send_name' => false,
        'send_email' => false,
        'proxy_launch' => false,
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
        'lti_version',
        'creator_launch_url',
        'consumer_key',
        'consumer_secret',
        'send_name',
        'send_email',
        'proxy_launch',
    ];

    /**
     * @return HasMany<LtiResource>
     */
    public function resources(): HasMany
    {
        return $this->hasMany(LtiResource::class);
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
