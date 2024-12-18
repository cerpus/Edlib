<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ContentUser extends Pivot
{
    protected $casts = [
        'role' => ContentRole::class,
    ];
}
