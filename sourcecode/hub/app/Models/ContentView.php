<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentViewSource;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentView extends Model
{
    use HasFactory;
    use HasUlids;

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
}
