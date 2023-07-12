<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $content_id
 * @property string $user_id
 * @property int $score
 * @property int $max_score
 * @property int $opened
 * @property int $finished
 * @property int $time
 * @property ?string $context
 */

class H5PResult extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'h5p_results';

    protected $fillable = [
        'content_id',
        'user_id',
        'score',
        'max_score',
        'opened',
        'finished',
        'time',
        'context'
    ];
}
