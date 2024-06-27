<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentViewSource;
use App\Support\HasUlidsFromCreationDate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    use HasFactory;
    use HasUlidsFromCreationDate;

    public const UPDATED_AT = null;

    protected $casts = [
        'source' => ContentViewSource::class,
    ];

    /**
     * @return BelongsTo<Content, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * @return BelongsTo<LtiPlatform, self>
     */
    public function ltiPlatform(): BelongsTo
    {
        return $this->belongsTo(LtiPlatform::class);
    }
}