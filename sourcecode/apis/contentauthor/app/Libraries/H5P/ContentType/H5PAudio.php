<?php

namespace App\Libraries\H5P\ContentType;

use App\Http\Libraries\License;

class H5PAudio extends BaseH5PContent
{
    protected $title = '';
    protected $library = 'H5P.Audio 1.3';
    protected $libraryId = 'AU';
    protected $contentTemplate = '{"params":{"playerMode":"full","fitToWrapper":true,"controls":true,"autoplay":false,"contentName":"Audio","audioNotSupported":"Your browser does not support this audio","files":[{"path":"","mime":"audio/mp3","copyright":{"license":"U"}}]},"metadata":{"license":"U","authors":[],"changes":[],"extraTitle":"","title":""}}';

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

    public function setAudioFileUrl($url)
    {
        $this->content->params->files[0]->path = $url;

        return $this;
    }

    public function setMimeType($mimeType)
    {
        $this->content->params->files[0]->mime = $mimeType;

        return $this;
    }

    public function setLicense($license)
    {
        $license = License::toH5PLicenseString($license);
        if ($license) {
            $this->license = $license;
            $this->content->params->files[0]->copyright->license = $license;
            $this->content->metadata->license = $license;
        }

        return $this;
    }
}
