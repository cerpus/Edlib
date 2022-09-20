<?php

namespace App\Events;

use App\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class QuestionsetWasSaved extends Event
{
    use SerializesModels;

    public $questionset;
    public $request;
    public $authId;
    public $reason;
    public $theSession;

    public function __construct(QuestionSet $questionset, Request $request, $authId, $reason, $theSession)
    {
        $this->questionset = $questionset;
        $this->request = $request;
        $this->authId = $authId;
        $this->reason = $reason;
        $this->theSession = $theSession;
    }
}
