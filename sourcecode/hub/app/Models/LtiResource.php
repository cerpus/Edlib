<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LtiResource extends Model
{
    use HasFactory;
    use HasUlids;

    public const UPDATED_AT = null;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'language_iso_639_3' => 'und',
    ];

    /**
     * @return BelongsTo<LtiTool, self>
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(LtiTool::class, 'lti_tool_id');
    }
}
