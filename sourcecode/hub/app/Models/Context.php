<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasUlidsFromCreationDate;
use Illuminate\Database\Eloquent\Model;

/**
 * A "context" is used to grant access based on externally defined criteria.
 */
class Context extends Model
{
    use HasUlidsFromCreationDate;

    protected $fillable = [
        'name',
    ];
}
