<?php

namespace App\Http\Libraries\ContentTypes;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ContentType create($title, $createUrl, $id, $description, $icon, $mainContentType)
 */
class ContentType
{
    use CreateTrait;

    public $title;
    public $createUrl;
    public $id;
    public $description;
    public $icon;
    public $mainContentType;

    private $subContentTypes = [];

    private $guarded = ['subContentTypes'];


    public function addSubContentTypes(array $subContentTypes)
    {
        $this->subContentTypes = $subContentTypes;
    }


    public function getSubContentTypes(): array
    {
        return $this->subContentTypes;
    }
}
