<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\LtiKeySet
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LtiKey[] $ltiKeys
 * @property-read int|null $lti_keys_count
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKeySet whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LtiRegistration[] $ltiRegistrations
 * @property-read int|null $lti_registrations_count
 * @property-read \App\Models\LtiKey|null $newestKey
 * @mixin IdeHelperLtiKeySet
 */
class LtiKeySet extends Model
{
    public function ltiKeys(): HasMany
    {
        return $this->hasMany(LtiKey::class);
    }

    public function newestKey(): HasOne
    {
        return $this->hasOne(LtiKey::class)->latestOfMany();
    }

    public function ltiRegistrations(): HasMany
    {
        return $this->hasMany(LtiRegistration::class);
    }
}
