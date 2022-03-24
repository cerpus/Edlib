<?php


namespace App\Libraries\DataObjects;

abstract class ContentStateDataObject
{
    public $id, $route, $_method, $_token;
    public $license, $title, $redirectToken;
    public $isPublished = false;
    public $isDraft = false;
    public $share = 'private';

    public function __construct()
    {
        $this->_token = csrf_token();
    }

}
