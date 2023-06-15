<?php

namespace App\Libraries\DataObjects;

use Cerpus\Helper\Traits\CreateTrait;

class Answer
{
    use CreateTrait;

    /**
     * @var string
     */
    public $text;

    /**
     * @var bool
     */
    public $correct = false;
}
