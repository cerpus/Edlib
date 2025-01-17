<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class EmailChanged extends Mailable
{
    use Queueable;

    public function __construct(
        private readonly string $name,
        private readonly string $oldEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('email-changed.subject'),
            to: [new Address($this->oldEmail, $this->name)],
        );
    }

    public function content(): Content
    {
        return new Content(html: 'emails.email-changed');
    }
}
