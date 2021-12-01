<?php

namespace App\Http\Libraries\ContentTypes;


interface ContentTypeInterface
{

    /**
     * @param $redirectToken
     * @return ContentType
     */
    public function getContentTypes($redirectToken);

    //public function store();
}
