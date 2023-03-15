<?php

namespace App\Http\Libraries\ContentTypes;

class TextContentType implements ContentTypeInterface
{
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(
            trans("common.text-content-type"),
            "article/create?redirectToken=$redirectToken",
            "articleId",
            '',
            "insert_photo",
            'article'
        );
    }
}
