<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property ?string $remember_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class Administrator extends Authenticatable
{
}
