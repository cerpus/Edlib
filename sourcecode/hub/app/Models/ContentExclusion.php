<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\HasUlidsFromCreationDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentExclusion extends Model
{
    use HasUlidsFromCreationDate;

    public const string|null UPDATED_AT = null;

    protected $dateFormat = 'Y-m-d H:i:s.uP';

    protected $fillable = ['content_id', 'exclude_from', 'user_id'];

    /**
     * @return BelongsTo<Content, $this>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
