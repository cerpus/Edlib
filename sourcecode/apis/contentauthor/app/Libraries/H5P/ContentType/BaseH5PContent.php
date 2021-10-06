<?php

namespace App\Libraries\H5P\ContentType;


use App\Libraries\NDLA\Importers\Handlers\Helpers\LicenseHelper;
use App\Libraries\NDLA\Importers\Handlers\Helpers\H5PMetadataHelper;

abstract class BaseH5PContent
{
    use LicenseHelper, H5PMetadataHelper;

    protected $id;
    protected $title, $maxScore;
    protected $action = 'create';
    protected $copyright = 0;
    protected $sharing = 'sharing';
    protected $license = 'BY';
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
        $this->id = $this->libraryId . '-' . $hashId ? md5($id) : $id;

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
