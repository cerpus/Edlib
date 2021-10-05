<?php

namespace App\Libraries\H5P\ContentType;

class CerpusImage extends BaseH5PContent
{
    protected $library = 'H5P.CerpusImage 1.0';
    protected $libraryId = 'CI';
    protected $contentTemplate = '{"params":{"contentName":"Image","file":{"path":"images/file-5ce394f38c360.jpg#tmp","mime":"image/jpeg","copyright":{"license":"U", "version": "4.0"},"width":640,"height":796},"alt":"House in a forrest"},"metadata":{"license":"U", "authors":[],"changes":[],"extraTitle":"Isolated house","title":"Isolated house"}}';

    public function setTitle($title)
    {
        $this->title = $title;

        $this->content->params->contentName = $this->title;
        $this->content->metadata->title = $this->title;
        $this->content->metadata->extraTitle = $this->title;

        return $this;
    }

    public function setAltText($altText)
    {
        $this->content->params->alt = $altText;

        return $this;
    }

    public function setHoverText($hoverText)
    {
        $this->content->params->file->title = $hoverText;

        return $this;
    }

    public function setImageUrl($url)
    {
        $this->content->params->file->path = $url;

        return $this;
    }

    public function setImageWidth($width)
    {
        $this->content->params->file->width = $width;

        return $this;
    }

    public function setImageHeight($height)
    {
        $this->content->params->file->height = $height;

        return $this;
    }

    public function setMimeType($mimeType)
    {
        $this->content->params->file->mime = $mimeType;

        return $this;
    }
}
