<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $content_id
 * @property string $user_id
 * @property int $sub_content_id
 * @property string $data_id
 * @property string $data
 * @property int $preload
 * @property int $invalidate
 * @property Carbon $updated_at
 * @property ?string $context
 */

class H5PContentsUserData extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "h5p_contents_user_data";

    /**
     * @return BelongsTo<H5PContent, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class, 'content_id');
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfContext(Builder $query, string $context): void
    {
        $this->scopeOfContexts($query, [$context]);
    }

    /**
     * @param Builder<self> $query
     */
    public function scopeOfContexts(Builder $query, array $contexts): void
    {
        $query->whereIn('context', array_map(function ($context) {
            return trim($context);
        }, $contexts));
    }

    public function getData()
    {
        return !empty($this->data) ? json_decode($this->data) : null;
    }
}
