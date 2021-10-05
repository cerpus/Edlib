<?php

namespace App\Events;

use App\Events\Event;
use App\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class QuestionsetWasSaved extends Event
{
    use SerializesModels;

    public $questionset, $request, $authId, $reason, $theSession;

    public function __construct(QuestionSet $questionset, Request $request, $authId, $reason, $theSession)
    {
        $this->questionset = $questionset;
        $this->request = $request;
        $this->authId = $authId;
        $this->reason = $reason;
        $this->theSession = $theSession;
    }
}
