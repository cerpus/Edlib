<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasUlidsFromCreationDate;
use Database\Factories\ContextFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A "context" is used to grant access based on externally defined criteria.
 */
class Context extends Model
{
    /** @use HasFactory<ContextFactory> */
    use HasFactory;
    use HasUlidsFromCreationDate;

    public const UPDATED_AT = null;

    protected $fillable = [
        'name',
    ];
}
