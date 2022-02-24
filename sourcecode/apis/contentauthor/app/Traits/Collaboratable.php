<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Collaborator;
use App\Mail\AddedAsCollaboratorMail;

/**
 * @property Collection<Collaborator> collaborators
 */

trait Collaboratable
{
    private $newCollaborators = [];
    private $addedCollaborators = [];

    public function collaborators()
    {
        return $this->morphMany('App\Collaborator', 'collaboratable');
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

    public function notifyNewCollaborators()
    {
        if (config('feature.collaboration')) {
            collect($this->newCollaborators)->each(function ($newCollaborator) {
                $mailData = new \stdClass();
                $mailData->emailTo = $newCollaborator;
                $mailData->inviterName = Session::get('name', trans('email.name-generic'));
                $mailData->contentTitle = $this->title;
                $mailData->originSystemName = Session::get('originalSystem', 'EdLib');
                $mailData->emailTitle = trans('emails/collaboration-invite.email-title',
                    ['originSystemName' => $mailData->originSystemName]);

                $loginUrl = 'https://edstep.com/';
                $emailFrom = 'no-reply@edlib.com';
                switch (mb_strtolower($mailData->originSystemName)) {
                    case 'edstep':
                        $loginUrl = 'https://edstep.com/';
                        $emailFrom = 'no-reply@edstep.com';
                        break;
                    case 'learnplayground':
                    case 'gamilab':
                        $loginUrl = 'https://gamilab.com/';
                        $emailFrom = 'no-reply@gamilab.com';
                        break;
                }
                $mailData->loginUrl = $loginUrl;
                $mailData->emailFrom = $emailFrom;

                Mail::to($mailData->emailTo)->queue(new AddedAsCollaboratorMail($mailData));
            });
        }

        return $this;
    }

    public function getCollaboratorEmails()
    {
        return implode(',', $this->collaborators->pluck('email')->toArray());
    }
}
