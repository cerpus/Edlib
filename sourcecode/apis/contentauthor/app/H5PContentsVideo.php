<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $h5p_content_id
 * @property string $video_id
 * @property string $source_file
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */

class H5PContentsVideo extends Model
{
    use HasFactory;

    protected $table = 'h5p_contents_video';

    protected $fillable = [
        'h5p_content_id',
        'video_id',
        'source_file'
    ];

    /**
     * @return BelongsTo<H5PContent, self>
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(H5PContent::class);
    }
}
