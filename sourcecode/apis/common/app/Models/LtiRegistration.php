<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\LtiRegistration
 *
 * @property int $id
 * @property string|null $issuer
 * @property string|null $client_id
 * @property string|null $platform_login_auth_endpoint
 * @property string|null $platform_auth_token_endpoint
 * @property string|null $platform_key_set_endpoint
 * @property int|null $lti_key_set_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LtiKeySet|null $ltiKeySet
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereIssuer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereLtiKeySetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration wherePlatformAuthTokenEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration wherePlatformKeySetEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration wherePlatformLoginAuthEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiRegistration whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LtiDeployment[] $ltiDeployments
 * @property-read int|null $lti_deployments_count
 */
class LtiRegistration extends Model
{
    protected $fillable = [
        'issuer',
        'client_id',
        'platform_login_auth_endpoint',
        'platform_auth_token_endpoint',
        'platform_key_set_endpoint',
    ];

    public function ltiKeySet(): BelongsTo
    {
        return $this->belongsTo(LtiKeySet::class);
    }

    public function ltiDeployments(): HasMany
    {
        return $this->hasMany(LtiDeployment::class);
    }
}
