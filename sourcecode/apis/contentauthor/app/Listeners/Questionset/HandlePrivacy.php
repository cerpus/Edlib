<?php

namespace App\Listeners\Questionset;

use App\Events\QuestionsetWasSaved;

class HandlePrivacy
{
    public function handle(QuestionsetWasSaved $event)
    {
        /** @var \App\QuestionSet $questionset */
        $questionset = $event->questionset->fresh();
        $request = $event->request;
        $private = $request->get('share', "PRIVATE");
        $isPrivate = (mb_strtoupper($private) === 'PRIVATE');
        $questionset->is_private = $isPrivate; // Yey it made a comeback...keeping it was worth it! :)
        $questionset->save();
    }
}
