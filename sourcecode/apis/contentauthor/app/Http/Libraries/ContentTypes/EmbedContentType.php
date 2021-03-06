<?php

namespace App\Http\Libraries\ContentTypes;

class EmbedContentType implements ContentTypeInterface
{

    /**
     * @param $redirectToken
     * @return ContentType
     */
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(trans("embed.link"),
            "embed/create?redirectToken=$redirectToken",
            "embedId",
            "",
            "link",
            "embed");
    }
}