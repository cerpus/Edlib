<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class H5PContentsVideo extends Model
{
    protected $table = 'h5p_contents_video';

    protected $fillable = [
        'h5p_content_id',
        'video_id',
        'source_file'
    ];

    public function content()
    {
        return $this->belongsTo(H5PContent::class);
    }
}
