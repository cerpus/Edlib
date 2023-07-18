<?php

namespace App\Http\Libraries\ContentTypes;

interface ContentTypeInterface
{
    /**
     * @return ContentType|ContentType[]
     */
    public function getContentTypes($redirectToken);

    //public function store();
}
