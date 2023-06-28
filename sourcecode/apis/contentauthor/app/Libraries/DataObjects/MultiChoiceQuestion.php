<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

class MultiChoiceQuestion extends Question
{
    use CreateTrait;

    /**
     * @var string
     */
    protected $type = 'H5P.MultiChoice';
}
