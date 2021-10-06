<?php

namespace App\Libraries\NDLA\Importers;


interface ImporterInterface
{
    public function import($json);

    public function setImportId($importId) : ImporterInterface;

    public function setDuplicateAction($forceInsert);
}
