<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBulkExclude extends Model
{
    use HasUlids;

    protected $dateFormat = 'Y-m-d H:i:s.u';
    public const string|null UPDATED_AT = null;

    public const string BULKACTION_BULK_UPGRADE = 'content_bulk_upgrade';
    public const string BULKACTION_LIBRARY_TRANSLATION = 'library_translation_update';

    public function content(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class);
    }
}
