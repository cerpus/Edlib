<?php

namespace App\Http\Libraries\ContentTypes;

interface ContentTypeInterface
{
    /**
     * @return ContentType
     */
    public function getContentTypes($redirectToken);

    //public function store();
}
