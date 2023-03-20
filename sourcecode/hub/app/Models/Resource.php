<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resource extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * @return BelongsTo<LtiTool, self>
     */
    public function ltiTool(): BelongsTo
    {
        return $this->belongsTo(LtiTool::class);
    }
}
