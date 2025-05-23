<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use App\Collaborator;

/**
 * @property Collection<Collaborator> $collaborators
 */

trait Collaboratable
{
    private $newCollaborators = [];
    private $addedCollaborators = [];

    public function collaborators()
    {
        return $this->morphMany(Collaborator::class, 'collaboratable');
    }

    public function setCollaborators($collaborators = [])
    {
        if (config('feature.collaboration')) {
            // Save the list of old collaborators
            $oldCollaborators = $this->collaborators->map(function ($collaborator) {
                return mb_strtolower($collaborator->email);
            })->toArray();

            // Remove all old collaborators
            $this->collaborators()->delete();

            // Add the new collaborators with valid email addresses
            collect($collaborators)->filter(function ($collaborator) {
                return filter_var($collaborator, FILTER_VALIDATE_EMAIL);
            })->tap(function ($collaborators) {
                $this->addedCollaborators = $collaborators;
            })->each(function ($collaborator) {
                $this->collaborators()->save(new Collaborator(['email' => $collaborator]));
            });

            // Make a list of new collaborators.
            $this->newCollaborators = $this->addedCollaborators
                ->reject(function ($collaborator) use ($oldCollaborators) {
                    return in_array(mb_strtolower($collaborator), $oldCollaborators);
                })->toArray();
        }

        return $this;
    }


    public function newCollaborators(): array
    {
        return $this->newCollaborators;
    }

    public function getCollaboratorEmails()
    {
        return implode(',', $this->collaborators->pluck('email')->toArray());
    }
}
