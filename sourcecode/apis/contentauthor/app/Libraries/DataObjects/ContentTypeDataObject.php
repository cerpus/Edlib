<?php

namespace App\Libraries\DataObjects;


class ContentTypeDataObject
{
    public $externalSystemName = "contentauthor";
    public $group;
    public $contentType;
    public $title;
    public $icon;

    public function __construct(
        string $group,
        string $contentType,
        string $title,
        ?string $icon
    )
    {
        $this->group = $group;
        $this->contentType = $contentType;
        $this->title = $title;
        $this->icon = $icon;
    }
}
