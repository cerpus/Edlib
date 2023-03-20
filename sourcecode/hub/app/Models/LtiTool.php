<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LtiTool extends Model
{
    use HasFactory;
    use HasUlids;

    public $timestamps = false;

    /**
     * @return HasMany<LtiResource>
     */
    public function resources(): HasMany
    {
        return $this->hasMany(LtiResource::class);
    }
}
