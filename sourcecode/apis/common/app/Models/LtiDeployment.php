<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LtiDeployment
 *
 * @property string $deployment_id
 * @property int $lti_registration_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment whereDeploymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment whereLtiRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiDeployment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LtiDeployment extends Model
{
    use HasFactory;

    protected $fillable = [
        'deployment_id',
    ];

    public function ltiRegistration(): BelongsTo
    {
        return $this->belongsTo(LtiRegistration::class);
    }
}
