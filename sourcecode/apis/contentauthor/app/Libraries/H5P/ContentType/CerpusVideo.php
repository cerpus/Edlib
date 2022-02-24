<?php

namespace App\Libraries\H5P\ContentType;

use App\Http\Libraries\License;

class CerpusVideo extends BaseH5PContent
{
    protected $title;
    protected $library = 'H5P.CerpusVideo 1.0';
    protected $libraryId = 'CV';
    protected $contentTemplate = '{"params":{"visuals":{"fit":false,"controls":true},"playback":{"autoplay":false,"loop":false},"l10n":{"name":"Video","loading":"Video player loading...","noPlayers":"Found no video players that supports the given video format.","noSources":"Video is missing sources.","aborted":"Media playback has been aborted.","networkFailure":"Network failure.","cannotDecode":"Unable to decode media.","formatNotSupported":"Video format not supported.","mediaEncrypted":"Media encrypted.","unknownError":"Unknown error.","invalidYtId":"Invalid YouTube ID.","unknownYtId":"Unable to find video with the given YouTube ID.","restrictedYt":"The owner of this video does not allow it to be embedded."},"sources":[{"path":"https://youtu.be/aMn7kKiax2A","mime":"video/YouTube","copyright":{"license":"U"}}]},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"YouTubeVideo","title":"YouTubeVideo"}}';

    public function setTitle($title)
    {
        $this->title = $title;

        $this->content->params->contentName = $this->title;
        $this->content->metadata->title = $this->title;
        $this->content->metadata->extraTitle = $this->title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setLicense($license)
    {
        $license = License::toH5PLicenseString($license);
        if ($license) {
            $this->license = $license;
            $this->content->params->sources[0]->copyright->license = $license;
            $this->content->metadata->license = $license;
            if (strstr($license, License::LICENSE_BY)) {
                $this->content->params->sources[0]->copyright->version = '4.0';
                $this->content->metadata->licenseVersion = '4.0';

            } else {
                unset($this->content->params->sources[0]->copyright->version);
                $this->content->metadata->licenseVersion = '';
            }
        }

        return $this;
    }

    public function setYouTubeVideoUrl($url)
    {
        $videoSourceObject = (object)[
            'path' => $url,
            'mime' => 'video/YouTube',
            'copyright' => (object)[
                'license' => 'U'
            ]
        ];

        $this->content->params->sources[0] = $videoSourceObject;

        return $this;
    }

    public function setBrightcoveVideoUrl($url)
    {
        $videoSourceObject = (object)[
            'path' => $url,
            'mime' => 'video/Brightcove',
            'copyright' => (object)[
                'license' => 'U'
            ]
        ];

        $this->content->params->sources[0] = $videoSourceObject;

        return $this;
    }

}
