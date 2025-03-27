<?php

namespace App\Libraries\DataObjects;

abstract class ContentStateDataObject
{
    public $id;
    public $route;
    public $_method;
    public $_token;
    public $license;
    public $title;
    public $redirectToken;
    public $isPublished = false;
    public $isDraft = false;
    public $isShared = false;

    public function __construct()
    {
        $this->_token = csrf_token();
    }
}
