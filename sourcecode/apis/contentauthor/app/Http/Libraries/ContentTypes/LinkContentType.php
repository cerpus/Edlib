<?php

namespace App\Http\Libraries\ContentTypes;

class LinkContentType implements ContentTypeInterface
{
    /**
     * Return an array with title an id for each contenttype provided
     * @return array
     */
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(
            trans("link.link"),
            "link/create?redirectToken=$redirectToken",
            "linkId",
            '',
            "link",
            'link'
        );
    }
}
