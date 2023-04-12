<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use App\Collaborator;

/**
 * @property Collection<Collaborator> $collaborators
 */
trait Collaboratable
{
    public function collaborators()
    {
        return $this->morphMany(Collaborator::class, 'collaboratable');
    }
}
