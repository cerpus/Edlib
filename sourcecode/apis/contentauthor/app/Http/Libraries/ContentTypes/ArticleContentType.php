<?php

namespace App\Http\Libraries\ContentTypes;

use App\Http\Libraries\ContentTypes\ContentType;

class ArticleContentType implements ContentTypeInterface
{

    /**
     * Return an array with title an id for each contenttype provided
     * @return array
     */
    public function getContentTypes($redirectToken)
    {
        return [new ContentType(trans("article.Article"),
            "article/create?redirectToken=$redirectToken",
            "articleId",
            '',
            "fa fa-newspaper-o")];
    }
}