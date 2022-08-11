<?php


namespace App\Libraries\H5P\Dataobjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static H5PTranslationDataObject create($attributes = null)
 */
class H5PTranslationDataObject
{
    use CreateTrait;

    public $id;
    private $document = [];

    public function setField($fieldId, $fieldValue)
    {
        $this->document[$fieldId] = $fieldValue;
    }

    public function setFieldsFromArray(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setField($key, $value);
        }
    }

    public function getDocument()
    {
        return $this->document;
    }
}
