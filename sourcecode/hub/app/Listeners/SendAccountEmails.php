<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserSaved;
use App\Mail\EmailChanged;
use App\Mail\VerifyEmailAddress;
use Illuminate\Contracts\Mail\Mailer;

final readonly class SendAccountEmails
{
    public function __construct(private Mailer $mailer) {}

    public function handleUserSaved(UserSaved $event): void
    {
        if ($event->user->email_verified) {
            // nothing to do
            return;
        }

        if ($event->user->wasRecentlyCreated) {
            $this->sendEmailToNewAccount($event);
        } elseif ($event->user->wasChanged('email')) {
            $this->sendEmailsOnEmailChange($event);
        }
    }

    private function sendEmailToNewAccount(UserSaved $event): void
    {
        // TODO: should be a welcome email
        $this->mailer->send(new VerifyEmailAddress($event->user));
    }

    private function sendEmailsOnEmailChange(UserSaved $event): void
    {
        // do not send the notification if the previous email was unverified
        if ($event->user->getOriginal('email_verified')) {
            $this->mailer->send(
                new EmailChanged(
                    oldEmail: $event->user->getOriginal('email'),
                    name: $event->user->getOriginal('name'),
                ),
            );
        }

        // send a verification request to the new address
        // TODO: should acknowledge the email change
        $this->mailer->send(new VerifyEmailAddress($event->user));
    }
}
