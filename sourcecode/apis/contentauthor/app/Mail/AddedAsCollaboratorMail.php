<?php

namespace App\Mail;

use Illuminate\Support\Facades\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddedAsCollaboratorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $collaborationData = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($collaborationData = [])
    {
        $this->collaborationData = $collaborationData;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->collaborationData->emailFrom, $this->collaborationData->originSystemName)
            ->subject($this->collaborationData->emailTitle)
            ->view('emails.collaboration-invite', ['mailData' => $this->collaborationData]);
    }
}
