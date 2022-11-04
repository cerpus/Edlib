<?php

namespace App\Libraries\H5P\Interfaces;

interface H5PAudioInterface
{
    public function findAudio($filterParameters);

    public function getAudio($audioId, array $params = []);
}
