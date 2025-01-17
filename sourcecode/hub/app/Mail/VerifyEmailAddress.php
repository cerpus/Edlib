<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class VerifyEmailAddress extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('verify-email.subject', ['site' => config('app.name')]),
            to: [new Address($this->user->email, $this->user->name)],
        );
    }

    public function content(): Content
    {
        return new Content(html: 'emails.verify-email', with: [
            'verification_link' => $this->user->makeVerificationLink(),
        ]);
    }
}
