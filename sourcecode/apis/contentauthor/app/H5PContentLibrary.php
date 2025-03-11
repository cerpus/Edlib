<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PContentLibrary extends Model
{
    use HasFactory;

    protected $table = 'h5p_contents_libraries';

    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return BelongsTo<H5PLibrary, $this>
     */
    public function library(): BelongsTo
    {
        return $this->belongsTo(H5PLibrary::class, 'library_id');
    }
}
