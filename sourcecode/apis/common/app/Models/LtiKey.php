<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LtiKeys
 *
 * @property int $id
 * @property int $lti_key_set_id
 * @property string $algorithm
 * @property string $private_key
 * @property string $public_key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey query()
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey whereAlgorithm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey whereLtiKeySetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LtiKey whereUpdatedAt($value)
 * @mixin \Eloquent
 * @mixin IdeHelperLtiKey
 */
class LtiKey extends Model
{
}
