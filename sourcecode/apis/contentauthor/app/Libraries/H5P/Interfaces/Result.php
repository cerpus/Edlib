<?php
namespace App\Libraries\H5P\Interfaces;

interface Result
{

    public function handleResult($userId, $contentId, $score, $maxScore, $opened, $closed, $time, $context);

}