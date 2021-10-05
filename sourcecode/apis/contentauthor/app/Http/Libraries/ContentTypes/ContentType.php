<?php

namespace App\Http\Libraries\ContentTypes;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class ContentType
 * @package App\Http\Libraries\ContentTypes
 *
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

    /**
     * @param array $subContentTypes
     */
    public function addSubContentTypes(array $subContentTypes)
    {
        $this->subContentTypes = $subContentTypes;
    }

    /**
     * @return array
     */
    public function getSubContentTypes(): array
    {
        return $this->subContentTypes;
    }


}