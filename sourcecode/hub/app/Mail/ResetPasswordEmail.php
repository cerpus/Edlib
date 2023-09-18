<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public string $resetLink)
    {
    }

    public function build(): Mailable
    {
        return $this->view('emails.reset-password')
            ->subject(trans('messages.reset-password'));
    }
}
