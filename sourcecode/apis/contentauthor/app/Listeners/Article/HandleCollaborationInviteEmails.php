<?php

namespace App\Listeners\Article;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class HandleCollaborationInviteEmails
{
    protected $mailer;

    public function __construct(Mail $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle($event)
    {
        if (array_key_exists('locale', $event->theSession)) {
            App::setLocale($event->theSession['locale']);
        }

        $mailer = $this->mailer;
        $article = $event->article->fresh();
        $oldCollaborators = $event->originalCollaborators->pluck('email')->toArray();

        $article->collaborators
            ->pluck('email')// All emails in new article
            ->filter(function ($newCollaborator) use ($oldCollaborators) {
                //Remove emails that exist as collaborators in the old article
                return !in_array($newCollaborator, $oldCollaborators);
            })->each(function ($collaborator) use ($mailer, $event) {
                if ($collaborator) {// Send mails to the new additions
                    $mailData = new \stdClass();
                    $mailData->emailTo = $collaborator;
                    $mailData->inviterName = $event->theSession['name'] ?? '';
                    $mailData->contentTitle = $event->article->title;
                    $mailData->originSystemName = array_key_exists('originalSystem', $event->theSession) ? $event->theSession['originalSystem'] : 'edLib';
                    $mailData->emailTitle = trans(
                        'emails/collaboration-invite.email-title',
                        ['originSystemName' => $mailData->originSystemName]
                    );

                    $loginUrl = 'https://edstep.com/';
                    $emailFrom = 'no-reply@edlib.com';
                    switch (mb_strtolower($mailData->originSystemName)) {
                        case 'edstep':
                            $loginUrl = 'https://edstep.com/';
                            $emailFrom = 'no-reply@edstep.com';
                            break;
                        case 'learnplayground':
                            $loginUrl = 'https://learnplayground.com/';
                            $emailFrom = 'no-reply@learnplayground.com';
                            break;
                    }
                    $mailData->loginUrl = $loginUrl;
                    $mailData->emailFrom = $emailFrom;

                    Mail::send(
                        'emails.collaboration-invite',
                        ['mailData' => $mailData],
                        function ($m) use ($mailData) {
                            $m->from($mailData->emailFrom, $mailData->originSystemName);
                            $m->to($mailData->emailTo)->subject($mailData->emailTitle);
                        }
                    );
                }
            });
    }
}
