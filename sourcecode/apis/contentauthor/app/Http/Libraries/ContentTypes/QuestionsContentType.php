<?php

namespace App\Http\Libraries\ContentTypes;

class QuestionsContentType implements ContentTypeInterface
{
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(
            trans("questions.questions"),
            "questionset/create?redirectToken=$redirectToken",
            "questionId",
            "",
            "insert_photo",
            "questionset"
        );
    }
}
