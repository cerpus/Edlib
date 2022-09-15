<?php

namespace App\Http\Libraries\ContentTypes;

use App\H5PLibrary;

class InteractivityContentType implements ContentTypeInterface
{
    /**
     * @param $redirectToken
     */
    public function getContentTypes($redirectToken): ContentType
    {
        /** @var ContentType $contentType */
        $contentType = ContentType::create(
            trans("common.interactivity-content-type"),
            route('h5p.create', ['redirectToken' => $redirectToken], false),
            "",
            '',
            "insert_photo",
            'h5p'
        );

        $subContentTypes = H5PLibrary::with(['capability' => function ($query) {
            $query->active();
        }])
            ->runnable()
            ->select('name')
            ->pluck('name')
            ->mapWithKeys(function ($library) use ($redirectToken) {
                return [$library => route('create.h5pContenttype', ['contenttype' => $library, 'redirectToken' => $redirectToken])];
            })
            ->toArray();
        $contentType->addSubContentTypes($subContentTypes);
        return $contentType;
    }
}
