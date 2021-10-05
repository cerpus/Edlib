<?php

namespace App\Libraries\NDLA\Importers;


class ImportStatus
{
    public $id;

    public $status;

    public $checksum;

    public $report;

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return true;
        }
        return false;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return;
    }

}