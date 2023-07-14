<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $h5p_id
 * @property string $email
 *
 * @method static Builder|static select(array|mixed $columns = ['*'])
 * @method static Builder|static where(\Closure|string|array $column, mixed $operator = null, mixed $value = null, string $boolean = 'and')
 */
class H5PCollaborator extends Model
{
    use HasFactory;

    protected $table = 'cerpus_contents_shares';

    public function setUpdatedAt($value)
    {
        // Do nothing.
        return $this;
    }
}
