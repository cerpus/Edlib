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
    public $theSession;

    public function __construct(QuestionSet $questionset, Request $request, $authId, $theSession)
    {
        $this->questionset = $questionset;
        $this->request = $request;
        $this->authId = $authId;
        $this->theSession = $theSession;
    }
}
