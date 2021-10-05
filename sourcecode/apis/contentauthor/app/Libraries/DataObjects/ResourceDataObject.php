<?php

namespace App\Libraries\DataObjects;


class ResourceDataObject
{
    public $id;
    public $title;
    public $action;
    public $type;

    const ARTICLE = 'article';
    const H5P = 'h5p';
    const QUESTIONSET = 'questionset';
    const LINK = 'link';
    const GAME = 'game';

    public function __construct($id, $title, $action, $type)
    {
        $this->id = $id;
        $this->title = $title;
        $this->action = $action;
        $this->type = $type;
    }
}