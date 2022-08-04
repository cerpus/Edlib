<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSavedFilterChoice
 */
class SavedFilterChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
        'value',
    ];

    public function savedFilter(): BelongsTo
    {
        return $this->belongsTo(SavedFilter::class);
    }
}
