<?php

namespace App\Models;

use App\Lti\Oauth1\Oauth1Credentials;
use BadMethodCallException;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LtiTool extends Model
{
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    protected $casts = [
        'lti_version' => LtiVersion::class,
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
    ];

    /**
     * @return HasMany<LtiResource>
     */
    public function resources(): HasMany
    {
        return $this->hasMany(LtiResource::class);
    }

    public function getOauth1Credentials(): Oauth1Credentials
    {
        if ($this->lti_version !== LtiVersion::Lti1_1) {
            throw new BadMethodCallException(
                'Can only get OAuth 1.0 credentials for LTI 1.1 tools',
            );
        }

        return new Oauth1Credentials(
            $this->consumer_key,
            $this->consumer_secret,
        );
    }
}
