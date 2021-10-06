<?php

namespace App\Listeners\Questionset;

use App\ACL\ArticleAccess;
use App\Events\QuestionsetWasSaved;
use App\Http\Libraries\License;
use App\Events\ArticleWasSaved;

class HandleLicensing
{
    use ArticleAccess;

    protected $license;

    public function handle(QuestionsetWasSaved $event)
    {
        try {
            $questionset = $event->questionset->fresh();
            $request = $event->request;

            $this->license = app()->make(License::class);
            $licenseContent = $this->license->getOrAddContent($questionset);
            if ($licenseContent) {
                $this->license->setLicense($request->input('license', 'BY'), $questionset->id);
            }
        } catch (Exception $e) {
            Log::error(__METHOD__ . ': Unable to add License to questionset ' . $questionset->id . '. ' . $e->getCode() . ': ' . $e->getMessage());
        }
    }
}
