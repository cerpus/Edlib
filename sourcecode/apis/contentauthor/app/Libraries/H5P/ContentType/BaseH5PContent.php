<?php

namespace App\Libraries\H5P\ContentType;

use App\Http\Libraries\License;

abstract class BaseH5PContent
{
    protected $id;
    protected $title;
    protected $maxScore;
    protected $action = 'create';
    protected $copyright = 0;
    protected $sharing = 'sharing';
    protected $license = License::LICENSE_BY;
    protected $library = '';
    protected $libraryId = '';
    protected $contentTemplate = '{}';
    protected $content;

    public function __construct()
    {
        $this->content = json_decode($this->contentTemplate);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Unable to decode content template");
        }
    }

    public function setId($id, $hashId = true)
    {
        $this->id = $this->libraryId . '-' . ($hashId ? md5($id) : $id);

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImportJson()
    {
        return (object)[
            'h5p_lib' => $this->library,
            'h5p_content_data' => $this->content,
            'title' => $this->title,
            'content_type' => 'h5p_content',
            'nodeId' => $this->id,
            'license' => (object)[
                'license' => $this->license,
            ]
        ];
    }
}
