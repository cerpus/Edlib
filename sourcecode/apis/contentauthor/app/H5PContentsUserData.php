<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class H5PContentsUserData extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = "h5p_contents_user_data";

    public function content()
    {
        return $this->belongsTo(H5PContent::class, 'content_id');
    }

    /**
     * @param Builder $query
     * @param string $context
     * @return mixed
     */
    public function scopeOfContext($query, string $context)
    {
        return $this->scopeOfContexts($query, [$context]);
    }

    /**
     * @param Builder $query
     * @param array $context
     * @return mixed
     */
    public function scopeOfContexts($query, array $contexts)
    {
        return $query->whereIn('context', array_map(function ($context) {
            return trim($context);
        }, $contexts));
    }

    public function getData()
    {
        return !empty($this->data) ? json_decode($this->data) : null;
    }
}
