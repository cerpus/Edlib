<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property  string $email
 * @property string $collaboratable_id
 * @property string $collaboratable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class Collaborator extends Model
{
    use HasFactory;

    protected $fillable = ['email'];

    public function collaboratable(): MorphTo
    {
        return $this->morphTo();
    }
}
