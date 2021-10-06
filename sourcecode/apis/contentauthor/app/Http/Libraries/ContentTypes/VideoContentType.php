<?php

namespace App\Http\Libraries\ContentTypes;

use App\H5PLibrary;
use App\Http\Libraries\ContentTypes\ContentType;
use App\Libraries\H5P\Packages\InteractiveVideo;

class VideoContentType implements ContentTypeInterface
{

    /**
     * @param $redirectToken
     * @return ContentType
     */
    public function getContentTypes($redirectToken): ContentType
    {
        return ContentType::create(trans("common.video-content-type"),
            route("create.h5pContenttype", [
                'contenttype' => InteractiveVideo::$machineName,
                'redirectToken' => $redirectToken,
            ],false),
            InteractiveVideo::$machineName,
            '',
            "movie",
            'video');
    }
}