<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

/**
 * @method static ResourceUserDataObject create($attributes = null)
 */
class ResourceUserDataObject
{
    use CreateTrait;

    public const NAME_FORMAT = '%s %s';
    public const NAME_AND_EMAIL_FORMAT = self::NAME_FORMAT . '(%s)';

    public $firstname;
    public $lastname;
    public $email;
    public $id;

    public function getFullName()
    {
        return sprintf(self::NAME_FORMAT, $this->firstname, $this->lastname);
    }

    public function getNameAndEmail()
    {
        if (empty($this->email)) {
            return $this->getFullName();
        }
        return sprintf(self::NAME_AND_EMAIL_FORMAT, $this->firstname, $this->lastname, $this->email);
    }
}
