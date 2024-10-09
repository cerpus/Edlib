<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LtiToolExtra extends Model
{
    use HasFactory;
    use HasUlids;

    public const CREATED_AT = null;
    public const UPDATED_AT = null;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'admin' => false,
    ];

    protected $casts = [
        'admin' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'lti_launch_url',
        'admin',
    ];

    /**
     * @return BelongsTo<LtiTool, self>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(LtiTool::class, 'lti_tool_id');
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeForAdmins(Builder $query, bool $admin = true): void
    {
        $query->where('admin', $admin);
    }
}
