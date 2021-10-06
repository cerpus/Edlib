<?php

namespace App\Libraries\DataObjects;


use Cerpus\Helper\Traits\CreateTrait;

/**
 * Class ResourceUserDataObject
 * @package App\Libraries\DataObjects
 *
 * @method static ResourceUserDataObject create($attributes = null)
 */
class ResourceUserDataObject
{
    use CreateTrait;

    const NAME_FORMAT = '%s %s';
    const NAME_AND_EMAIL_FORMAT = self::NAME_FORMAT . '(%s)';

    public $firstname, $lastname, $email, $id;

    public function getFullName()
    {
        return sprintf(self::NAME_FORMAT, $this->firstname, $this->lastname);
    }

    public function getNameAndEmail()
    {
        if( empty($this->email)){
            return $this->getFullName();
        }
        return sprintf(self::NAME_AND_EMAIL_FORMAT, $this->firstname, $this->lastname, $this->email);
    }
}