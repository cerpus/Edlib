<?php

namespace App\Listeners\Questionset;

use App\ACL\ArticleAccess;
use App\Events\QuestionsetWasSaved;
use Cerpus\MetadataServiceClient\Exceptions\MetadataServiceException;
use Log;

class HandleTags
{

    /**
     * @param QuestionsetWasSaved $event
     * @return $this|bool
     * @throws MetadataServiceException
     */
    public function handle(QuestionsetWasSaved $event)
    {
        /** @var \App\QuestionSet $questionset */
        $questionset = $event->questionset->fresh();
        $request = $event->request;
        $tags = $request->get('tags', null);
        if( !empty($tags) ){
            return $questionset->updateMetaTags($tags);
        }
        return true;
    }
}
