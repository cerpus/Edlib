<?php

namespace App\Http\Libraries\ContentTypes;

class ArticleContentType implements ContentTypeInterface
{
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(
            trans("article.Article"),
            "article/create?redirectToken=$redirectToken",
            "articleId",
            '',
            "fa fa-newspaper-o",
            "article"
        );
    }
}
